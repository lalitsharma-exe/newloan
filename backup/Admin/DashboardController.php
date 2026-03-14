<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
class DashboardController extends Controller {
    public function __construct(private DashboardService $svc) {}
    public function index() {
        return view("admin.dashboard.index",[
            "stats"              => $this->svc->getStats(),
            "recentApplications" => $this->svc->getRecentApplications(10),
            "overdueLoans"       => $this->svc->getOverdueLoans(5),
            "recentPayments"     => $this->svc->getRecentPayments(5),
            "monthlyChart"       => $this->svc->getMonthlyChartData(),
        ]);
    }
}
