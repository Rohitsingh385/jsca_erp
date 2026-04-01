<?php
// app/Config/Routes.php
use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ─── Public routes ──────────────────────────────────────────
$routes->get('/',        'Auth::login');
$routes->get('login',    'Auth::login');
$routes->post('login',   'Auth::doLogin');
$routes->get('logout',   'Auth::logout');
$routes->get('forgot-password',  'Auth::forgotPassword');
$routes->post('forgot-password', 'Auth::sendReset');
$routes->get('reset-password/(:segment)',  'Auth::resetPassword/$1');
$routes->post('reset-password/(:segment)', 'Auth::doReset/$1');

// ─── Player self-registration (public) ──────────────────────
$routes->get('player-register',             'PlayerSelfRegister::index');
$routes->post('player-register/send-otp',   'PlayerSelfRegister::sendOtp');
$routes->get('player-register/verify-otp',  'PlayerSelfRegister::verifyOtpForm');
$routes->post('player-register/verify-otp', 'PlayerSelfRegister::verifyOtp');
$routes->get('player-register/form',        'PlayerSelfRegister::form');
$routes->post('player-register/submit',     'PlayerSelfRegister::submit');
$routes->get('player-register/success',     'PlayerSelfRegister::success');

// ─── Authenticated routes ────────────────────────────────────
$routes->group('', ['filter' => 'auth'], function ($routes) {

    // Official Dashboard
    $routes->group('official', ['filter' => 'auth'], function ($routes) {
        $routes->get('dashboard',               'OfficialDashboard::index');
        $routes->get('profile',                 'OfficialDashboard::profile');
        $routes->get('invoice/(:num)',          'OfficialDashboard::invoice/$1');
        $routes->post('request-payment/(:num)', 'OfficialDashboard::requestPayment/$1');
    });

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');

    // ── Players ──────────────────────────────────────────────
    $routes->group('players', ['filter' => 'rbac:players,players.view,players.create'], function ($routes) {
        $routes->get('/',              'Players::index');
        $routes->get('create',         'Players::create');
        $routes->post('store',         'Players::store');
        $routes->get('view/(:num)',    'Players::view/$1');
        $routes->get('edit/(:num)',    'Players::edit/$1');
        $routes->post('update/(:num)', 'Players::update/$1');
        $routes->post('delete/(:num)',         'Players::delete/$1');
        $routes->post('verify/(:num)',          'Players::verify/$1');
        $routes->get('verify-aadhaar/(:num)',   'Players::verifyAadhaar/$1');
        $routes->get('export',                  'Players::export');
        $routes->get('stats/(:num)',            'Players::stats/$1');
        $routes->post('upload-doc/(:num)',      'Players::uploadDoc/$1');
        $routes->post('verify-doc/(:num)',      'Players::verifyDoc/$1');
        $routes->post('delete-doc/(:num)',      'Players::deleteDoc/$1');
    });

    // ── Coaches ───────────────────────────────────────────────
    $routes->group('coaches', ['filter' => 'rbac:coaches,coaches.view,coaches.create'], function ($routes) {
        $routes->get('/',                       'Coaches::index');
        $routes->get('create',                  'Coaches::create');
        $routes->post('store',                  'Coaches::store');
        $routes->get('view/(:num)',             'Coaches::view/$1');
        $routes->get('edit/(:num)',             'Coaches::edit/$1');
        $routes->post('update/(:num)',          'Coaches::update/$1');
        $routes->post('delete/(:num)',          'Coaches::delete/$1');
        $routes->post('upload-doc/(:num)',      'Coaches::uploadDoc/$1');
        $routes->post('verify-doc/(:num)',      'Coaches::verifyDoc/$1');
        $routes->post('delete-doc/(:num)',      'Coaches::deleteDoc/$1');
    });

    // ── Teams ──────────────────────────────────────────────
    $routes->group('venues', ['filter' => 'rbac:venues,fixtures'], function ($routes) {
        $routes->get('/',              'Venues::index');
        $routes->get('create',         'Venues::create');
        $routes->post('store',         'Venues::store');
        $routes->get('view/(:num)',    'Venues::view/$1');
        $routes->get('edit/(:num)',    'Venues::edit/$1');
        $routes->post('update/(:num)', 'Venues::update/$1');
        $routes->post('toggle/(:num)', 'Venues::toggle/$1');
    });

    // ── Teams ───────────────────────────────────────────────
    $routes->group('teams', function ($routes) {
        $routes->get('/',                           'Teams::index');
        $routes->get('create',                      'Teams::create');
        $routes->post('store',                      'Teams::store');
        $routes->get('view/(:num)',                 'Teams::view/$1');
        $routes->get('edit/(:num)',                 'Teams::edit/$1');
        $routes->post('update/(:num)',              'Teams::update/$1');
        $routes->post('delete/(:num)',              'Teams::delete/$1');
        $routes->post('add-player/(:num)',          'Teams::addPlayer/$1');
        $routes->post('remove-player/(:num)/(:num)', 'Teams::removePlayer/$1/$2');
        $routes->post('add-coach/(:num)',           'Teams::addCoach/$1');
        $routes->post('remove-coach/(:num)/(:num)', 'Teams::removeCoach/$1/$2');
        $routes->post('upload-doc/(:num)',          'Teams::uploadDoc/$1');
        $routes->post('verify-doc/(:num)',          'Teams::verifyDoc/$1');
        $routes->post('delete-doc/(:num)',          'Teams::deleteDoc/$1');
    });

    // ── Tournaments ───────────────────────────────────────────
    $routes->group('tournaments', ['filter' => 'rbac:tournaments,fixtures'], function ($routes) {
        $routes->get('/',                           'Tournaments::index');
        $routes->get('create',                      'Tournaments::create');
        $routes->post('store',                      'Tournaments::store');
        $routes->get('view/(:num)',                 'Tournaments::view/$1');
        $routes->get('edit/(:num)',                 'Tournaments::edit/$1');
        $routes->post('update/(:num)',              'Tournaments::update/$1');
        $routes->post('delete/(:num)',              'Tournaments::delete/$1');
        $routes->post('update-status/(:num)',       'Tournaments::updateStatus/$1');
        $routes->get('teams/(:num)',                'Tournaments::teams/$1');
        $routes->post('add-team/(:num)',            'Tournaments::addTeam/$1');
        $routes->post('remove-team/(:num)/(:num)',  'Tournaments::removeTeam/$1/$2');
        $routes->post('upload-doc/(:num)',          'Tournaments::uploadDoc/$1');
        $routes->post('verify-doc/(:num)',          'Tournaments::verifyDoc/$1');
        $routes->post('delete-doc/(:num)',          'Tournaments::deleteDoc/$1');
    });

    // ── Fixtures ──────────────────────────────────────────────
    $routes->group('fixtures', ['filter' => 'rbac:fixtures,tournaments'], function ($routes) {
        $routes->get('/',                              'Fixtures::index');
        $routes->get('create',                         'Fixtures::create');
        $routes->post('store',                         'Fixtures::store');
        $routes->get('view/(:num)',                    'Fixtures::view/$1');
        $routes->get('edit/(:num)',                    'Fixtures::edit/$1');
        $routes->post('update/(:num)',                 'Fixtures::update/$1');
        $routes->post('delete/(:num)',                 'Fixtures::delete/$1');
        $routes->get('tournament/(:num)',              'Fixtures::tournament/$1');
        $routes->get('teams-for-tournament/(:num)',    'Fixtures::teamsForTournament/$1');
        $routes->get('officials-for-tournament/(:num)','Fixtures::officialsForTournament/$1');
        $routes->post('update-status/(:num)',          'Fixtures::updateStatus/$1');
    });

    // ── Matches / Scoring ─────────────────────────────────────
    $routes->group('matches', function ($routes) {
        $routes->get('live',                    'Matches::live');
        $routes->get('live/api-refresh',        'Matches::apiRefresh');
        $routes->post('live/store',             'Matches::storeLocal');
        $routes->post('live/update/(:num)',      'Matches::updateLocal/$1');
        $routes->post('live/delete/(:num)',      'Matches::deleteLocal/$1');
        $routes->get('score/(:num)',            'Matches::score/$1');
        $routes->post('save-score/(:num)',      'Matches::saveScore/$1');
        $routes->get('scorecard/(:num)',        'Matches::scorecard/$1');
        $routes->post('complete/(:num)',        'Matches::complete/$1');
    });

    // ── Officials ─────────────────────────────────────────────
    $routes->group('officials', ['filter' => 'rbac:officials,fixtures'], function ($routes) {
        $routes->get('/',              'Officials::index');
        $routes->get('create',         'Officials::create');
        $routes->post('store',         'Officials::store');
        $routes->get('view/(:num)',    'Officials::view/$1');
        $routes->get('edit/(:num)',    'Officials::edit/$1');
        $routes->post('update/(:num)', 'Officials::update/$1');
        $routes->post('toggle/(:num)', 'Officials::toggle/$1');
    });

    // ── Finance ───────────────────────────────────────────────
    $routes->group('finance', function ($routes) {
        $routes->get('/',                       'Finance::index');
        $routes->get('vouchers',                'Finance::vouchers');
        $routes->get('voucher/create',          'Finance::createVoucher');
        $routes->post('voucher/store',          'Finance::storeVoucher');
        $routes->get('voucher/view/(:num)',     'Finance::viewVoucher/$1');
        $routes->post('voucher/approve/(:num)', 'Finance::approveVoucher/$1');
        $routes->post('voucher/reject/(:num)',  'Finance::rejectVoucher/$1');
        $routes->post('voucher/mark-paid/(:num)', 'Finance::markPaid/$1');
        $routes->get('auto-generate/(:num)',    'Finance::autoGenerate/$1');
        $routes->get('reports',                 'FinanceReports::index');
        $routes->get('reports/invoices',        'FinanceReports::invoices');
        $routes->get('reports/pending',         'FinanceReports::pending');
        $routes->get('reports/no-bank',         'FinanceReports::noBank');
        $routes->get('export',                  'Finance::export');
        $routes->get('voucher/rcpt_create',     'Finance::rcpt_create');
        $routes->post('voucher/getMatchesByTournament',     'Finance::getMatchesByTournament');
        $routes->post('voucher/getOfficialsByType', 'Finance::getOfficialsByType');
        $routes->post('voucher/final_save', 'Finance::final_save');
        $routes->get('voucher/print/(:num)', 'Finance::print_voucher/$1');
        $routes->get('finance/voucher/status/(:num)/(:any)', 'Finance::update_status/$1/$2');

        // Group Master
        $routes->get('accgroups', 'Finance::accgroups');
        $routes->post('accgroups/store', 'Finance::storeaccGroup');
        $routes->get('accgroups/edit/(:num)', 'Finance::editaccGroup/$1');
        $routes->post('accgroups/update/(:num)', 'Finance::updateaccGroup/$1');
        $routes->get('accgroups/deleteaccGroup/(:segment)', 'Finance::deleteaccGroup/$1');

        // Ledger Heads
        $routes->get('ledger-heads', 'Finance::ledgerHeads');
        $routes->post('ledger/store', 'Finance::storeLedger');
        $routes->get('ledger/edit/(:num)', 'Finance::editLedger/$1');
        $routes->post('ledger/update/(:num)', 'Finance::updateLedger/$1');
        $routes->get('ledger/delete/(:num)', 'Finance::deleteLedger/$1');

        // Bank Master
        $routes->get('bank-master', 'BankController::index');
        $routes->post('bank-master/save', 'BankController::save');
        $routes->get('bank-master/delete/(:num)', 'BankController::delete/$1');
    });
    // ── Analytics ─────────────────────────────────────────────
    $routes->group('analytics', function ($routes) {
        $routes->get('/',                    'Analytics::index');
        $routes->get('players',             'Analytics::players');
        $routes->get('tournaments',         'Analytics::tournaments');
        $routes->get('venues',              'Analytics::venues');
        $routes->get('officials',           'Analytics::officials');
        $routes->get('player/(:num)',       'Analytics::player/$1');
    });

    // ── Reports ───────────────────────────────────────────────
    $routes->group('reports', function ($routes) {
        $routes->get('tournament/(:num)', 'Reports::tournament/$1');
        $routes->get('finance',           'Reports::finance');
        $routes->get('players',           'Reports::players');
        $routes->get('season',            'Reports::season');
    });

    // ── Users / Admin ─────────────────────────────────────────
    $routes->group('admin', function ($routes) {
        $routes->get('users',                'Admin::users');
        $routes->get('users/create',         'Admin::createUser');
        $routes->get('users/people-by-role', 'Admin::peopleByRole');
        $routes->post('users/store',         'Admin::storeUser');
        $routes->get('users/edit/(:num)',    'Admin::editUser/$1');
        $routes->post('users/update/(:num)', 'Admin::updateUser/$1');
        $routes->post('users/toggle/(:num)', 'Admin::toggleUser/$1');
        $routes->get('audit-log',            'Admin::auditLog');
    });

    // ── Profile ───────────────────────────────────────────────
    $routes->get('profile',       'Profile::index');
    $routes->post('profile/save', 'Profile::save');
});

// ─── REST API routes (for mobile / CricHeroes sync) ─────────
$routes->group('api/v1', ['filter' => 'api_auth'], function ($routes) {
    $routes->get('players',             'Api\Players::index');
    $routes->get('players/(:num)',      'Api\Players::show/$1');
    $routes->get('fixtures/(:num)',     'Api\Fixtures::byTournament/$1');
    $routes->post('scores/(:num)',      'Api\Scores::store/$1');
    $routes->get('stats/player/(:num)', 'Api\Stats::player/$1');
    $routes->get('tournaments',         'Api\Tournaments::index');
    $routes->get('tournaments/(:num)',  'Api\Tournaments::show/$1');
});
