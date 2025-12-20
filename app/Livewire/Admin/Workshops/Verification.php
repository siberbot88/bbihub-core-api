<?php

namespace App\Livewire\Admin\Workshops;

use App\Models\Workshop;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Verifikasi Bengkel')]
#[Layout('layouts.app')]
class Verification extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    // Documents State
    public bool $showDocuments = false;
    public $selectedDocuments = null;

    // Confirmation State
    public bool $showVerifyModal = false;
    public bool $showRejectModal = false;
    public ?Workshop $workshopToProcess = null;

    // --- Document Methods ---

    public function openDocuments($id)
    {
        $workshop = Workshop::with('document')->findOrFail($id);
        $this->selectedDocuments = $workshop->document;
        $this->showDocuments = true;
    }

    public function closeDocuments()
    {
        $this->showDocuments = false;
        $this->selectedDocuments = null;
    }

    // --- Verification Methods ---

    public function promptVerify($id)
    {
        $this->workshopToProcess = Workshop::findOrFail($id);
        $this->showVerifyModal = true;
    }

    public function confirmVerify()
    {
        if ($this->workshopToProcess) {
            $this->workshopToProcess->update(['status' => 'active']);
            session()->flash('message', "Bengkel {$this->workshopToProcess->name} berhasil diverifikasi! ✅");
        }

        $this->resetConfirmation();
    }

    // --- Rejection Methods ---

    public function promptReject($id)
    {
        $this->workshopToProcess = Workshop::findOrFail($id);
        $this->showRejectModal = true;
    }

    public function confirmReject()
    {
        if ($this->workshopToProcess) {
            $this->workshopToProcess->update(['status' => 'rejected']);
            session()->flash('message', "Bengkel {$this->workshopToProcess->name} telah ditolak. ❌");
        }

        $this->resetConfirmation();
    }

    public function resetConfirmation()
    {
        $this->showVerifyModal = false;
        $this->showRejectModal = false;
        $this->workshopToProcess = null;
    }

    public function render()
    {
        $workshops = Workshop::where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.workshops.verification', [
            'workshops' => $workshops
        ]);
    }
}
