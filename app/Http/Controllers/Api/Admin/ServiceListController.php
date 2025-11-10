<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceLog;
use Illuminate\Http\Request;

class ServiceListController extends Controller
{
    // LIST untuk Scheduled/Logging
    public function index(Request $r)
    {
        $r->validate([
            'status'   => 'nullable|in:requested,rejected,pending,"in progress",completed,paid',
            'date'     => 'nullable|date',
            'q'        => 'nullable|string',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $q = Service::query()
            ->with(['workshop','customer','vehicle','mechanic'])
            ->when($r->status, fn($s)=>$s->where('status', $r->status))
            ->when($r->date,   fn($s)=>$s->whereDate('scheduled_date', $r->date))
            ->when($r->q, function($s) use ($r){
                $kw = '%'.$r->q.'%';
                $s->where(function($w) use ($kw){
                    $w->where('code','like',$kw)
                        ->orWhere('name','like',$kw)
                        ->orWhereHas('customer', fn($c)=>$c->where('name','like',$kw))
                        ->orWhereHas('vehicle',  fn($v)=>$v->where('plate_number','like',$kw));
                });
            })
            ->latest('scheduled_date');

        $data = $q->paginate($r->input('per_page', 12));

        return response()->json([
            'message' => 'OK',
            'data'    => $data->items(),
            'meta'    => [
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'total'        => $data->total(),
            ],
        ], 200);
    }

    public function show(Service $service)
    {
        $service->load(['workshop','customer','vehicle','mechanic','logs' => fn($l)=>$l->latest()]);
        return response()->json(['message'=>'OK', 'data'=>$service], 200);
    }

    // ========= AKSI SESUAI ALUR =========

    // requested -> pending
    public function accept(Request $r, Service $service)
    {
        $r->validate(['notes'=>'nullable|string']);
        abort_if($service->status !== 'requested', 422, 'Hanya dari requested.');
        $service->update(['status'=>'pending']);
        $service->logs()->create(['notes'=>$r->input('notes','Accepted by admin')]);
        return response()->json(['message'=>'Accepted','data'=>$service->fresh()], 200);
    }

    // requested -> rejected
    public function reject(Request $r, Service $service)
    {
        $r->validate(['notes'=>'nullable|string']);
        abort_if($service->status !== 'requested', 422, 'Hanya dari requested.');
        $service->update(['status'=>'rejected']);
        $service->logs()->create(['notes'=>$r->input('notes','Rejected by admin')]);
        return response()->noContent(); // 204
    }

    // pending -> (tetap pending; mekanik terisi)
    public function assignMechanic(Request $r, Service $service)
    {
        $r->validate(['mechanic_uuid'=>'required|uuid|exists:users,id','notes'=>'nullable|string']);
        abort_if($service->status !== 'pending', 422);
        $service->update(['mechanic_uuid'=>$r->mechanic_uuid]); // status tetap pending
        $service->logs()->create(['mechanic_uuid'=>$r->mechanic_uuid,'notes'=>$r->input('notes','Assigned mechanic')]);
        return response()->json(['message'=>'Assigned','data'=>$service->fresh()], 200);
    }

    // pending -> in progress
    public function startWork(Service $service)
    {
        abort_if($service->status !== 'pending', 422);
        abort_if(!$service->mechanic_uuid, 422, 'Mechanic belum ditetapkan.');
        $service->update(['status'=>'in progress']);
        $service->logs()->create(['mechanic_uuid'=>$service->mechanic_uuid,'notes'=>'Work started']);
        return response()->json(['message'=>'Started','data'=>$service->fresh()], 200);
    }

    // in progress -> completed
    public function finishWork(Service $service)
    {
        abort_if($service->status !== 'in progress', 422);
        $service->update(['status'=>'completed']);
        $service->logs()->create(['mechanic_uuid'=>$service->mechanic_uuid,'notes'=>'Completed']);
        return response()->json(['message'=>'Completed','data'=>$service->fresh()], 200);
    }

    // completed -> paid
    public function confirmPayment(Service $service)
    {
        abort_if($service->status !== 'completed', 422);
        $service->update(['status'=>'paid']);
        $service->logs()->create(['notes'=>'Payment confirmed']);
        return response()->json(['message'=>'Paid','data'=>$service->fresh()], 200);
    }
}
