<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Title('Dashboard')]
// Bisa juga kirim prop ke layout: ['title' => 'Dashboard']
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public array $cards = [];
    public array $activityLogs = [];
    public array $serviceMonthly = [];
    public array $revenueByWorkshop = [];

    public function mount(): void
    {
        $this->cards = [
            [
                'title' => 'Total Bengkel',
                'value' => 20,
                'desc' => 'Bengkel terkonfirmasi',
                'icon' => 'bengkel',
                'delta' => '+5%',
                'chart' => [10, 15, 12, 18, 20, 15, 20]
            ],
            [
                'title' => 'Total User',
                'value' => 120,
                'desc' => 'Pelanggan terdaftar',
                'icon' => 'pengguna',
                'delta' => '+12%',
                'chart' => [80, 90, 100, 95, 110, 115, 120]
            ],
            [
                'title' => 'Total Teknisi',
                'value' => 45,
                'desc' => 'Mekanik terverifikasi',
                'icon' => 'tech',
                'delta' => '+8%',
                'chart' => [30, 32, 35, 38, 40, 42, 45]
            ],
            [
                'title' => 'Total Feedback',
                'value' => 23,
                'desc' => 'Feedback hari ini',
                'icon' => 'feedback',
                'delta' => '+2%',
                'chart' => [15, 18, 12, 20, 18, 22, 23]
            ],
        ];

        $this->activityLogs = [
            ['title'=>'Ahmad Rizki, berhasil verifikasi sebagai mekanik','time'=>'2 menit yang lalu'],
            ['title'=>'Bengkel ABC baru mendaftar','time'=>'5 menit yang lalu'],
        ];

        $this->serviceMonthly = [
            'labels' => ['Jan','Feb','Mar','Apr','Mei','Jun','Jul'],
            'data'   => [20,45,80,30,28,40,120],
        ];

        $this->revenueByWorkshop = [
            'labels' => ['Auto Fix','Moto Fix','Quick Fix','Speed Garage','Elit Motor'],
            'data'   => [120000,50000,30000,90000,40000],
        ];
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
