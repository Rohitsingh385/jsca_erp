<?php
// app/Controllers/Dashboard.php
namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Dashboard — JSCA ERP',

            // Counts
            'totalPlayers'      => $this->db->table('players')->where('status', 'Active')->countAllResults(),
            'verifiedPlayers'   => $this->db->table('players')->where('aadhaar_verified', 1)->countAllResults(),
            'totalOfficials'    => $this->db->table('officials')->where('status', 'Active')->countAllResults(),
            'totalTournaments'  => $this->db->table('tournaments')->countAllResults(),
            'activeTournaments' => $this->db->table('tournaments')->whereIn('status', ['Ongoing', 'Fixture Ready'])->countAllResults(),
            'totalMatches'      => $this->db->table('fixtures')->countAllResults(),
            'completedMatches'  => $this->db->table('fixtures')->where('status', 'Completed')->countAllResults(),
            'pendingVouchers'   => $this->db->table('vouchers')->where('status', 'Pending Approval')->countAllResults(),
            'totalDisbursed'    => $this->db->table('vouchers')->where('status', 'Paid')->selectSum('total_amount')->get()->getRowArray()['total_amount'] ?? 0,

            // Live matches
            'liveMatches' => $this->db->table('fixtures f')
                ->select('f.*, t.name as tournament_name, ta.name as team_a_name, tb.name as team_b_name, v.name as venue_name')
                ->join('tournaments t', 't.id = f.tournament_id')
                ->join('teams ta', 'ta.id = f.team_a_id')
                ->join('teams tb', 'tb.id = f.team_b_id')
                ->join('venues v', 'v.id = f.venue_id')
                ->where('f.status', 'Live')
                ->get()->getResultArray(),

            // Upcoming matches (next 7 days)
            'upcomingMatches' => $this->db->table('fixtures f')
                ->select('f.*, t.name as tournament_name, ta.name as team_a_name, tb.name as team_b_name, v.name as venue_name, v.has_floodlights')
                ->join('tournaments t', 't.id = f.tournament_id')
                ->join('teams ta', 'ta.id = f.team_a_id')
                ->join('teams tb', 'tb.id = f.team_b_id')
                ->join('venues v', 'v.id = f.venue_id')
                ->where('f.status', 'Scheduled')
                ->where('f.match_date >=', date('Y-m-d'))
                ->where('f.match_date <=', date('Y-m-d', strtotime('+7 days')))
                ->orderBy('f.match_date', 'ASC')
                ->orderBy('f.match_time', 'ASC')
                ->limit(8)
                ->get()->getResultArray(),

            // Recent activity
            'recentActivity' => $this->db->table('audit_logs al')
                ->select('al.*, u.full_name')
                ->join('users u', 'u.id = al.user_id', 'left')
                ->orderBy('al.created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray(),

            // Top scorers this season
            'topScorers' => $this->db->table('batting_stats bs')
                ->select('p.full_name, p.jsca_player_id, d.name as district, SUM(bs.runs) as total_runs, COUNT(DISTINCT bs.fixture_id) as innings')
                ->join('players p', 'p.id = bs.player_id')
                ->join('districts d', 'd.id = p.district_id')
                ->groupBy('bs.player_id')
                ->orderBy('total_runs', 'DESC')
                ->limit(5)
                ->get()->getResultArray(),

            // Category breakdown
            'categoryBreakdown' => $this->db->table('players')
                ->select('age_category, COUNT(*) as count')
                ->where('status', 'Active')
                ->groupBy('age_category')
                ->get()->getResultArray(),

            // Overdue fixtures — past date but still Scheduled
            'overdueFixtures' => $this->db->table('fixtures f')
                ->select('f.*, t.name as tournament_name, ta.name as team_a_name, tb.name as team_b_name')
                ->join('tournaments t', 't.id = f.tournament_id')
                ->join('teams ta', 'ta.id = f.team_a_id')
                ->join('teams tb', 'tb.id = f.team_b_id')
                ->where('f.status', 'Scheduled')
                ->where('f.match_date <', date('Y-m-d'))
                ->orderBy('f.match_date', 'ASC')
                ->get()->getResultArray(),

            // Pending vouchers list
            'pendingVouchersList' => $this->db->table('vouchers pv')
                ->select('pv.*, u.full_name as created_by_name')
                ->join('users u', 'u.id = pv.created_by', 'left')
                ->where('pv.status', 'Pending Approval')
                ->orderBy('pv.created_at', 'ASC')
                ->limit(5)
                ->get()->getResultArray(),
        ];

        return $this->render('dashboard/index', $data);
    }
}
