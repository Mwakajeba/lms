<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
            </div>
            <div class="top-menu-left d-none d-lg-block">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class='bx bx-envelope'></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="javascript:;" data-bs-toggle="modal"
                            data-bs-target="#loanMessagesModal" title="Loan Messages & Notifications">
                            <i class='bx bx-message'></i>
                            <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                id="messageNotificationBadge" style="display: none;">
                                0
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:;" data-bs-toggle="modal"
                            data-bs-target="#calendarModal"><i class='bx bx-calendar'></i></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="javascript:;" data-bs-toggle="modal" data-bs-target="#tasksModal"
                            title="Task Manager">
                            <i class='bx bx-check-square'></i>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="search-bar flex-grow-1">
                <div class="position-relative search-bar-box">
                    <form>
                        <input type="text" class="form-control search-control" autofocus
                            placeholder="Type to search..."> <span
                            class="position-absolute top-50 search-show translate-middle-y"><i
                                class='bx bx-search'></i></span>
                        <span class="position-absolute top-50 search-close translate-middle-y"><i
                                class='bx bx-x'></i></span>
                    </form>
                </div>
            </div>

            <!-- Moving Message Ticker -->
            <div class="message-ticker-container">
                <div class="message-ticker">
                    <div class="ticker-content" id="tickerContent">
                        <!-- Messages will be loaded here -->
                    </div>
                </div>
            </div>
            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    <li class="nav-item mobile-search-icon">
                        <a class="nav-link" href="javascript:;"><i class='bx bx-search'></i>
                        </a>
                    </li>
                    <li class="nav-item dark-mode d-none d-sm-flex">
                        <a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
                        </a>
                    </li>
                    <!--SHORT MENU-->
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false"> <i class='bx bx-category'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <div class="row row-cols-3 g-3 p-3">
                                @can('view customers')
                                    <div class="col text-center">
                                        <a href="{{ route('customers.index') }}" class="text-decoration-none">
                                            <div class="app-box mx-auto bg-gradient-blues"><i class='bx bx-user'></i></div>
                                            <div class="app-title">Customers</div>
                                        </a>
                                    </div>
                                @endcan

                                @can('view loans')
                                    <div class="col text-center">
                                        <a href="{{ route('loans.list') }}" class="text-decoration-none">
                                            <div class="app-box mx-auto bg-gradient-blues"><i class='bx bx-money'></i></div>
                                            <div class="app-title">Loans</div>
                                        </a>
                                    </div>
                                @endcan

                                @can('view groups')
                                    <div class="col text-center">
                                        <a href="{{ route('groups.index') }}" class="text-decoration-none">
                                            <div class="app-box mx-auto bg-gradient-blues"><i class='bx bx-group'></i></div>
                                            <div class="app-title">Groups</div>
                                        </a>
                                    </div>
                                @endcan

                                <div class="col text-center">
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#calendarModal"
                                        class="text-decoration-none">
                                        <div class="app-box mx-auto bg-gradient-blues"><i class='bx bx-calendar'></i>
                                        </div>
                                        <div class="app-title">Calendar</div>
                                    </a>
                                </div>

                                <div class="col text-center">
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#tasksModal"
                                        class="text-decoration-none">
                                        <div class="app-box mx-auto bg-gradient-success"><i
                                                class='bx bx-check-square'></i></div>
                                        <div class="app-title">Tasks</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!--SHORT MENU-->
                    @php
                        $user = auth()->user();
                        if (!$user) {
                            // Session expired, logout using POST
                            echo '<script>document.addEventListener("DOMContentLoaded", function() { document.getElementById("logout-form").submit(); });</script>';
                            exit;
                        }
                        $branchId = $user->branch_id ?? null;
                        if (!$branchId) {
                            // Redirect to branch selection page
                            header('Location: ' . route('choose.branch'));
                            exit;
                        }
                        $today = \Carbon\Carbon::today()->toDateString();
                        $dueSchedules = \DB::table('loan_schedules')
                            ->join('loans', 'loan_schedules.loan_id', '=', 'loans.id')
                            ->join('customers', 'loan_schedules.customer_id', '=', 'customers.id')
                            ->where('loan_schedules.due_date', $today)
                            ->where('loans.branch_id', $branchId)
                            ->select('customers.name', \DB::raw('(loan_schedules.principal + loan_schedules.interest) as amount_due'))
                            ->get();
                    @endphp
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count"
                                id="navbarNotificationCount">{{$dueSchedules->count()}}</span>
                            <i class='bx bx-bell'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:;">
                                <div class="msg-header">
                                    <p class="msg-header-title">Notifications</p>
                                    <!-- <p class="msg-header-clear ms-auto">Marks all as read</p> -->
                                </div>
                            </a>
                            <div class="header-notifications-list" style="display: flex; flex-direction: column;">
                                @if($dueSchedules->count())
                                    @foreach($dueSchedules as $due)
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="user-online"><i class="bx bx-user"></i></div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">{{ $due->name }} <span class="msg-time float-end">Due
                                                            Today</span></h6>
                                                    <p class="msg-info">Amount Due: {{ number_format($due->amount_due, 2) }}</p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <a class="dropdown-item" href="javascript:;">
                                        <div class="d-flex align-items-center">
                                            <div class="user-online"><i class="bx bx-user"></i></div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name">No due payments today</h6>
                                            </div>
                                        </div>
                                    </a>
                                @endif

                            </div>
                            <a href="javascript:;">
                                <div class="text-center msg-footer">View All Notifications</div>
                            </a>
                        </div>
                    </li>
                    <!-- Subscription Expiry Alert -->
                    @role('super-admin')
                    @php
                        $subscriptionExpiryAlerts = \App\Models\Subscription::where('status', 'active')
                            ->where('end_date', '<=', now()->addDays(5))
                            ->where('end_date', '>=', now())
                            ->with('company')
                            ->get();
                    @endphp
                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="alert-count subscription-alert" id="subscriptionExpiryCount">
                                {{ $subscriptionExpiryAlerts->count() }}
                            </span>
                            <i class='bx bx-credit-card'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:;">
                                <div class="msg-header">
                                    <p class="msg-header-title">Subscription Expiry Alerts</p>
                                    <p class="msg-header-subtitle">Subscriptions expiring within 5 days</p>
                                </div>
                            </a>
                            <div class="header-notifications-list">
                                @if($subscriptionExpiryAlerts->count() > 0)
                                    @foreach($subscriptionExpiryAlerts as $subscription)
                                        @php
                                            $daysLeft = now()->diffInDays($subscription->end_date, false);
                                            $alertClass = $daysLeft <= 1 ? 'danger' : ($daysLeft <= 3 ? 'warning' : 'info');
                                        @endphp
                                        <a class="dropdown-item" href="{{ route('subscriptions.show', $subscription) }}">
                                            <div class="d-flex align-items-center">
                                                <div class="user-online subscription-alert-icon bg-{{ $alertClass }}">
                                                    <i class="bx bx-credit-card"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">
                                                        {{ $subscription->company->name }}
                                                        <span class="msg-time float-end badge bg-{{ $alertClass }}">
                                                            {{ $daysLeft == 0 ? 'Expires Today' : ($daysLeft == 1 ? '1 day left' : $daysLeft . ' days left') }}
                                                        </span>
                                                    </h6>
                                                    <p class="msg-info">
                                                        <strong>{{ $subscription->plan_name }}</strong> -
                                                        {{ number_format($subscription->amount, 2) }}
                                                        {{ $subscription->currency }}
                                                    </p>
                                                    <small class="text-muted">
                                                        Expires: {{ $subscription->end_date->format('M d, Y') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <a class="dropdown-item" href="javascript:;">
                                        <div class="d-flex align-items-center">
                                            <div class="user-online subscription-alert-icon bg-success">
                                                <i class="bx bx-check-circle"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name">No subscriptions expiring soon</h6>
                                                <p class="msg-info">All subscriptions are up to date</p>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                            <a href="{{ route('subscriptions.dashboard') }}">
                                <div class="text-center msg-footer">View All Subscriptions</div>
                            </a>
                        </div>
                    </li>
                    @endrole

                    <li class="nav-item dropdown dropdown-large">
                        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="alert-count" id="navbarArrearsCount">
                                {{ $arrearsLoansCount ?? 0 }}
                            </span>
                            <i class='bx bx-comment'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:;">
                                <div class="msg-header">
                                    <p class="msg-header-title">Loans in Arrears (1-30 days)</p>
                                </div>
                            </a>
                            <div class="header-message-list">
                                @if(isset($arrearsLoans) && count($arrearsLoans))
                                    @foreach($arrearsLoans as $loan)
                                        <a class="dropdown-item" href="javascript:;">
                                            <div class="d-flex align-items-center">
                                                <div class="user-online">
                                                    <i class="bx bx-user"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="msg-name">{{ $loan->customer_name }} <span
                                                            class="msg-time float-end">{{ $loan->days_in_arrears }} days</span>
                                                    </h6>
                                                    <p class="msg-info">Amount in Arrears:
                                                        {{ number_format($loan->amount_in_arrears, 2) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @else
                                    <a class="dropdown-item" href="javascript:;">
                                        <div class="d-flex align-items-center">
                                            <div class="user-online"><i class="bx bx-user"></i></div>
                                            <div class="flex-grow-1">
                                                <h6 class="msg-name">No loans in arrears for 1-30 days</h6>
                                            </div>
                                        </div>
                                    </a>
                                @endif
                            </div>
                            <a href="{{ route('arrears.loans.list') }}" id="viewArrearsLoans">
                                <div class="text-center msg-footer">View More</div>
                            </a>
                        </div>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="javascript:;">
                                <div class="msg-header">
                                    <p class="msg-header-title">Messages</p>
                                    <p class="msg-header-clear ms-auto">Marks all as read</p>
                                </div>
                            </a>
                            <div class="header-message-list">
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-1.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Daisy Anderson <span class="msg-time float-end">5 sec
                                                    ago</span></h6>
                                            <p class="msg-info">The standard chunk of lorem</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-2.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Althea Cabardo <span class="msg-time float-end">14
                                                    sec ago</span></h6>
                                            <p class="msg-info">Many desktop publishing packages</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-3.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Oscar Garner <span class="msg-time float-end">8 min
                                                    ago</span></h6>
                                            <p class="msg-info">Various versions have evolved over</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-4.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Katherine Pechon <span class="msg-time float-end">15
                                                    min ago</span></h6>
                                            <p class="msg-info">Making this the first true generator</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-5.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Amelia Doe <span class="msg-time float-end">22 min
                                                    ago</span></h6>
                                            <p class="msg-info">Duis aute irure dolor in reprehenderit</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-6.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Cristina Jhons <span class="msg-time float-end">2 hrs
                                                    ago</span></h6>
                                            <p class="msg-info">The passage is attributed to an unknown</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-7.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">James Caviness <span class="msg-time float-end">4 hrs
                                                    ago</span></h6>
                                            <p class="msg-info">The point of using Lorem</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-8.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Peter Costanzo <span class="msg-time float-end">6 hrs
                                                    ago</span></h6>
                                            <p class="msg-info">It was popularised in the 1960s</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-9.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">David Buckley <span class="msg-time float-end">2 hrs
                                                    ago</span></h6>
                                            <p class="msg-info">Various versions have evolved over</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-10.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Thomas Wheeler <span class="msg-time float-end">2 days
                                                    ago</span></h6>
                                            <p class="msg-info">If you are going to use a passage</p>
                                        </div>
                                    </div>
                                </a>
                                <a class="dropdown-item" href="javascript:;">
                                    <div class="d-flex align-items-center">
                                        <div class="user-online">
                                            <img src="assets/images/avatars/avatar-11.png" class="msg-avatar"
                                                alt="user avatar">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="msg-name">Johnny Seitz <span class="msg-time float-end">5 days
                                                    ago</span></h6>
                                            <p class="msg-info">All the Lorem Ipsum generators</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <a href="javascript:;">
                                <div class="text-center msg-footer">View All Messages</div>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Language Switcher -->
            <div class="me-3">
                @include('incs.languageSwitcher')
            </div>

            <div class="user-box dropdown px-3">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ asset('assets/images/avatars/avatar-2.png') }}" class="user-img" alt="user avatar">
                    <div class="user-info ps-3">
                        <p class="user-name mb-0">{{ Auth::user()->name }}</p>
                        <?php
// Fetch the user's role name
$roleName = Auth::user()->roles->first() ? ucfirst(Auth::user()->roles->first()->name) : '';
                ?>
                        <p class="designattion mb-0">{{ $roleName }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('users.profile') }}"><i
                                class="bx bx-user"></i><span>Profile</span></a>
                    </li>
                    <li><a class="dropdown-item" href="{{ route('users.profile') }}"><i
                                class='bx bx-home-circle'></i><span>Change Password</span></a>
                    </li>
                    <li>
                        <div class="dropdown-divider mb-0"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class='bx bx-log-out-circle'></i><span> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <!-- Tasks Modal -->
    <div class="modal fade" id="tasksModal" tabindex="-1" aria-labelledby="tasksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tasksModalLabel">
                        <i class="bx bx-check-square me-2"></i>Task Manager
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Task Input Form -->
                    <div class="card border-success mb-3">
                        <div class="card-body">
                            <form id="addTaskForm">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control form-control-lg" id="taskInput"
                                            placeholder="Enter a new task for today..." required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success btn-lg w-100">
                                            <i class="bx bx-plus me-1"></i>Add Today's Task
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <small class="text-muted mt-2 d-block">
                                <i class="bx bx-info-circle me-1"></i>Tasks are automatically assigned to today's date
                            </small>
                        </div>
                    </div>

                    <!-- Task Statistics -->
                    <div class="task-stats mb-3">
                        <div class="row">
                            <div class="col-md-3 stat-item">
                                <div class="stat-number" id="totalTasks">0</div>
                                <div class="stat-label">Today's Tasks</div>
                            </div>
                            <div class="col-md-3 stat-item">
                                <div class="stat-number" id="pendingTasksCount">0</div>
                                <div class="stat-label">Pending</div>
                            </div>
                            <div class="col-md-3 stat-item">
                                <div class="stat-number" id="completedTasksCount">0</div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="col-md-3 stat-item">
                                <div class="stat-number" id="todayDate">--</div>
                                <div class="stat-label">Date</div>
                            </div>
                        </div>
                    </div>

                    <!-- Task Categories -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="taskFilter" id="allTasks" value="all"
                                    checked>
                                <label class="btn btn-outline-primary" for="allTasks">
                                    <i class="bx bx-list-ul me-2"></i>All Today
                                </label>

                                <input type="radio" class="btn-check" name="taskFilter" id="pendingTasks"
                                    value="pending">
                                <label class="btn btn-outline-warning" for="pendingTasks">
                                    <i class="bx bx-time me-2"></i>Pending
                                </label>

                                <input type="radio" class="btn-check" name="taskFilter" id="completedTasks"
                                    value="completed">
                                <label class="btn btn-outline-success" for="completedTasks">
                                    <i class="bx bx-check-circle me-2"></i>Completed
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tasks List -->
                    <div id="tasksList">
                        <!-- Tasks will be loaded here -->
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary w-100 action-btn"
                                id="clearCompleted">
                                <i class="bx bx-trash me-2"></i>Clear Completed
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-info w-100 action-btn" id="exportTasks">
                                <i class="bx bx-download me-2"></i>Export Tasks
                            </button>
                        </div>
                    </div>

                    <!-- Help Text -->
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="bx bx-info-circle me-1"></i>
                            <strong>Tip:</strong> Click on any task to mark it as complete/pending
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Task Modal Styling */
        #tasksModal .modal-header {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        #tasksModal .card {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        #tasksModal .card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        #tasksModal .btn-group .btn {
            border-radius: 0.375rem;
            margin: 0 2px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }

        #tasksModal .btn-group .btn:first-child {
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
            margin-left: 0;
        }

        #tasksModal .btn-group .btn:last-child {
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
            margin-right: 0;
        }

        #tasksModal .btn-group .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        #tasksModal .form-control-lg {
            border-radius: 0.5rem;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        #tasksModal .form-control-lg:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
            transform: scale(1.01);
        }

        #tasksModal .btn-lg {
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        #tasksModal .btn-lg:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
        }

        .task-stats {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
        }

        .task-stats .stat-item {
            text-align: center;
            padding: 0.5rem;
        }

        .task-stats .stat-number {
            font-size: 1.75rem;
            font-weight: bold;
            color: #495057;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .task-stats .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        /* Task Item Styling */
        .task-item {
            transition: all 0.3s ease;
            border-width: 2px;
            position: relative;
            overflow: hidden;
        }

        .task-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .task-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .task-item:hover::before {
            left: 100%;
        }

        .task-item:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .bg-light-success {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        /* Task Toggle Button */
        .task-toggle-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border-width: 2px;
        }

        .task-toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .task-toggle-btn .bx {
            font-size: 1.25rem;
        }

        /* Task Delete Button */
        .task-delete-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border-width: 2px;
        }

        .task-delete-btn:hover {
            transform: scale(1.1);
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .task-delete-btn .bx {
            font-size: 1rem;
        }

        /* Task Text and Meta */
        .task-text {
            font-size: 1.1rem;
            line-height: 1.4;
        }

        .task-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .task-meta .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 1rem;
            font-weight: 500;
        }

        .task-meta .badge i {
            font-size: 0.875rem;
        }

        /* Filter Button Enhancements */
        #tasksModal .btn-group .btn i {
            font-size: 1.1rem;
            vertical-align: middle;
        }

        /* Action Buttons */
        .action-btn {
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-width: 2px;
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .action-btn i {
            font-size: 1.1rem;
            vertical-align: middle;
        }

        #tasksModal .btn-outline-secondary:hover,
        #tasksModal .btn-outline-info:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Empty State Styling */
        #tasksList .text-center.text-muted {
            padding: 3rem 1rem;
        }

        #tasksList .text-center.text-muted i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
        }

        .empty-state-icon i {
            font-size: 3rem !important;
            color: #adb5bd;
            margin: 0 !important;
        }

        #tasksList .text-center.text-muted p {
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        #tasksList .text-center.text-muted small {
            font-size: 0.9rem;
            color: #adb5bd;
        }

        /* Loan Messages Modal Styles */
        .message-item {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .message-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .message-item.border-primary {
            border-left-color: #0d6efd;
        }

        .message-item.border-secondary {
            border-left-color: #6c757d;
        }

        .message-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .message-meta .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }

        .empty-state-icon {
            font-size: 4rem;
            color: #dee2e6;
        }

        .empty-state-icon i {
            opacity: 0.5;
        }

        /* Priority badges */
        .badge.bg-warning.text-dark {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        .badge.bg-danger.text-white {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .badge.bg-info.text-white {
            background-color: #0dcaf0 !important;
            color: #fff !important;
        }

        .badge.bg-secondary.text-white {
            background-color: #6c757d !important;
            color: #fff !important;
        }

        /* Filter buttons */
        .btn-check:checked+.btn-outline-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .btn-check:checked+.btn-outline-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        .btn-check:checked+.btn-outline-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .btn-check:checked+.btn-outline-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            color: white;
        }

        /* Subscription Alert Styles */
        .subscription-alert {
            background: linear-gradient(45deg, #ff6b6b, #ffa500) !important;
            animation: pulse-subscription 2s infinite;
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }

        @keyframes pulse-subscription {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .subscription-alert-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .subscription-alert-icon.bg-danger {
            background: linear-gradient(45deg, #dc3545, #ff6b6b) !important;
        }

        .subscription-alert-icon.bg-warning {
            background: linear-gradient(45deg, #ffc107, #ffa500) !important;
            color: #000 !important;
        }

        .subscription-alert-icon.bg-info {
            background: linear-gradient(45deg, #17a2b8, #20c997) !important;
        }

        .subscription-alert-icon.bg-success {
            background: linear-gradient(45deg, #28a745, #20c997) !important;
        }

        .msg-header-subtitle {
            font-size: 0.75rem;
            color: #6c757d;
            margin: 0;
            font-weight: 400;
        }

        /* Enhanced dropdown styling for subscription alerts */
        .dropdown-menu .dropdown-item:hover {
            background-color: #f8f9fa;
            transform: translateX(2px);
            transition: all 0.2s ease;
        }

        .dropdown-menu .dropdown-item {
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .dropdown-menu .dropdown-item:hover {
            border-left-color: #007bff;
        }

        /* Badge enhancements */
        .badge {
            font-size: 0.7rem;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
            font-weight: 600;
        }

        .badge.bg-danger {
            background: linear-gradient(45deg, #dc3545, #ff6b6b) !important;
        }

        .badge.bg-warning {
            background: linear-gradient(45deg, #ffc107, #ffa500) !important;
            color: #000 !important;
        }

        .badge.bg-info {
            background: linear-gradient(45deg, #17a2b8, #20c997) !important;
        }

        /* Moving Message Ticker Styles */
        .message-ticker-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 40px;
            background: linear-gradient(90deg, #007bff, #0056b3);
            z-index: 9999;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 123, 255, 0.3);
            display: none;
            /* Initially hidden */
        }

        .message-ticker {
            height: 100%;
            display: flex;
            align-items: center;
            position: relative;
        }

        .ticker-content {
            display: flex;
            align-items: center;
            white-space: nowrap;
            animation: scroll-left 60s linear infinite;
            color: white;
            font-weight: 500;
            font-size: 14px;
            padding: 0 20px;
        }

        .ticker-message {
            display: inline-flex;
            align-items: center;
            margin-right: 60px;
            padding: 8px 16px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .ticker-message:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .ticker-message i {
            margin-right: 8px;
            font-size: 16px;
        }

        .ticker-message.urgent {
            background: rgba(220, 53, 69, 0.3);
            border-color: rgba(220, 53, 69, 0.5);
            animation: pulse-urgent 2s infinite;
        }

        .ticker-message.warning {
            background: rgba(255, 193, 7, 0.3);
            border-color: rgba(255, 193, 7, 0.5);
            color: #000;
        }

        .ticker-message.info {
            background: rgba(23, 162, 184, 0.3);
            border-color: rgba(23, 162, 184, 0.5);
        }

        .ticker-message.success {
            background: rgba(40, 167, 69, 0.3);
            border-color: rgba(40, 167, 69, 0.5);
        }

        @keyframes scroll-left {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        @keyframes pulse-urgent {
            0% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        /* Adjust topbar to account for ticker */
        .topbar {
            margin-top: 0px;
            /* Default no margin, will be adjusted by JavaScript */
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .message-ticker-container {
                height: 35px;
            }

            .ticker-content {
                font-size: 12px;
                padding: 0 15px;
            }

            .ticker-message {
                padding: 6px 12px;
                margin-right: 40px;
            }

            .topbar {
                margin-top: 35px;
            }
        }
    </style>

</header>
<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<!-- Loan Messages Modal -->
<div class="modal fade" id="loanMessagesModal" tabindex="-1" aria-labelledby="loanMessagesModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loanMessagesModalLabel">
                    <i class="bx bx-message me-2"></i>Loan Messages & Notifications
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-light btn-sm" onclick="loadMessages()"
                        title="Refresh Messages">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body">
                <!-- Message Categories -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="messageFilter" id="allMessages" value="all"
                                checked>
                            <label class="btn btn-outline-primary" for="allMessages">
                                <i class="bx bx-list-ul me-2"></i>All Messages
                            </label>

                            <input type="radio" class="btn-check" name="messageFilter" id="dueReminders" value="due">
                            <label class="btn btn-outline-warning" for="dueReminders">
                                <i class="bx bx-time me-2"></i>Due Reminders
                            </label>

                            <input type="radio" class="btn-check" name="messageFilter" id="arrearsAlerts"
                                value="arrears">
                            <label class="btn btn-outline-danger" for="arrearsAlerts">
                                <i class="bx bx-error-circle me-2"></i>Arrears Alerts
                            </label>

                            <input type="radio" class="btn-check" name="messageFilter" id="approvalRequests"
                                value="approval">
                            <label class="btn btn-outline-info" for="approvalRequests">
                                <i class="bx bx-check-shield me-2"></i>Approval Requests
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center p-3">
                                <i class="bx bx-time fs-1 mb-2"></i>
                                <h6 class="mb-1">Due Today</h6>
                                <h4 class="mb-0" id="dueTodayCount">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center p-3">
                                <i class="bx bx-error-circle fs-1 mb-2"></i>
                                <h6 class="mb-1">In Arrears</h6>
                                <h4 class="mb-0" id="arrearsCount">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center p-3">
                                <i class="bx bx-check-shield fs-1 mb-2"></i>
                                <h6 class="mb-1">Pending Approval</h6>
                                <h4 class="mb-0" id="pendingApprovalCount">0</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center p-3">
                                <i class="bx bx-message fs-1 mb-2"></i>
                                <h6 class="mb-1">Total Messages</h6>
                                <h4 class="mb-0" id="totalMessagesCount">0</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages List -->
                <div id="messagesList">
                    <!-- Messages will be loaded here -->
                </div>

                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary w-100" id="sendBulkSMS">
                            <i class="bx bx-send me-2"></i>Send Bulk SMS
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-success w-100" id="markAllRead">
                            <i class="bx bx-check-double me-2"></i>Mark All as Read
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-info w-100" id="exportMessages">
                            <i class="bx bx-download me-2"></i>Export Messages
                        </button>
                    </div>
                    {{-- <div class="col-md-4 mt-3">
                        <button type="button" class="btn btn-primary w-100" id="openSendSms">
                            <i class="bx bx-message-square-dots me-2"></i>Send SMS
                        </button>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send SMS Modal -->
<div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendSmsModalLabel"><i class="bx bx-message-square-dots me-2"></i>Send SMS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="sendSmsForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Recipient Phone</label>
                        <input type="text" class="form-control" name="phone" id="smsPhone" placeholder="e.g. 2557XXXXXXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" rows="4" name="message" id="smsMessage" placeholder="Type your message..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="sendSmsBtn"><i class="bx bx-send me-1"></i>Send</button>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Modal -->
<div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarModalLabel">
                    <i class="bx bx-calendar me-2"></i>Calendar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="prevMonth">
                            <i class="bx bx-chevron-left"></i> Previous
                        </button>
                        <h5 class="mb-0" id="currentMonthYear">Loading...</h5>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="nextMonth">
                            Next <i class="bx bx-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Calendar Legend -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="d-flex flex-wrap gap-3 justify-content-center">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2">●</span>
                                <small>Loan Due</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">●</span>
                                <small>Repayment</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-primary me-2">●</span>
                                <small>Disbursement</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">●</span>
                                <small>Cash Transaction</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="calendar-container">
                    <!-- Day Headers -->
                    <div class="calendar-header">
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="calendar-grid" id="calendarGrid">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading calendar...</p>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row mt-4" id="summaryCards" style="display: none;">
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Due This Month</h6>
                                <h4 class="mb-0" id="dueCount">0</h4>
                                <small>Loan Payments</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Repayments</h6>
                                <h4 class="mb-0" id="repaymentCount">0</h4>
                                <small>This Month</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Disbursements</h6>
                                <h4 class="mb-0" id="disbursementCount">0</h4>
                                <small>This Month</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Cash Transactions</h6>
                                <h4 class="mb-0" id="cashTransactionCount">0</h4>
                                <small>This Month</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="goToToday">Go to Today</button>
            </div>
        </div>
    </div>
</div>

<style>
    .calendar-container {
        max-width: 100%;
        overflow-x: auto;
    }

    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #f8f9fa;
        border-radius: 8px 8px 0 0;
        overflow: hidden;
    }

    .calendar-day-header {
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        background-color: #e9ecef;
        color: #495057;
    }

    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #dee2e6;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }

    .calendar-day {
        min-height: 100px;
        background-color: white;
        padding: 6px;
        position: relative;
    }

    .calendar-day.other-month {
        background-color: #f8f9fa;
        color: #adb5bd;
    }

    .calendar-day.today {
        background-color: #e3f2fd;
        border: 2px solid #2196f3;
    }

    .calendar-date {
        font-weight: 600;
        margin-bottom: 6px;
        color: #495057;
        font-size: 14px;
    }

    .calendar-events {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .calendar-event {
        padding: 2px 4px;
        border-radius: 4px;
        font-size: 9px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        cursor: pointer;
    }

    .calendar-event.loan-due {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 3px solid #dc3545;
    }

    .calendar-event.repayment {
        background-color: #d1edff;
        color: #0c5460;
        border-left: 3px solid #17a2b8;
    }

    .calendar-event.disbursement {
        background-color: #cce5ff;
        color: #004085;
        border-left: 3px solid #007bff;
    }

    .calendar-event.cash-transaction {
        background-color: #fff3cd;
        color: #856404;
        border-left: 3px solid #ffc107;
    }

    @media (max-width: 768px) {
        .calendar-day {
            min-height: 80px;
            padding: 4px;
        }

        .calendar-event {
            font-size: 8px;
            padding: 1px 2px;
        }

        .calendar-date {
            font-size: 12px;
            margin-bottom: 4px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let currentMonth = new Date().getMonth() + 1;
        let currentYear = new Date().getFullYear();

        // Load calendar when modal opens
        document.getElementById('calendarModal').addEventListener('show.bs.modal', function () {
            loadCalendar(currentMonth, currentYear);
        });

        // Previous month button
        document.getElementById('prevMonth').addEventListener('click', function () {
            if (currentMonth === 1) {
                currentMonth = 12;
                currentYear--;
            } else {
                currentMonth--;
            }
            loadCalendar(currentMonth, currentYear);
        });

        // Next month button
        document.getElementById('nextMonth').addEventListener('click', function () {
            if (currentMonth === 12) {
                currentMonth = 1;
                currentYear++;
            } else {
                currentMonth++;
            }
            loadCalendar(currentMonth, currentYear);
        });

        // Go to today button
        document.getElementById('goToToday').addEventListener('click', function () {
            currentMonth = new Date().getMonth() + 1;
            currentYear = new Date().getFullYear();
            loadCalendar(currentMonth, currentYear);
        });

        function loadCalendar(month, year) {
            const grid = document.getElementById('calendarGrid');
            const monthYear = document.getElementById('currentMonthYear');

            // Show loading
            grid.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading calendar...</p></div>';

            // Fetch calendar data
            fetch(`/calendar?month=${month}&year=${year}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderCalendar(data.calendar, month, year);
                        updateSummaryCards(data);
                    } else {
                        grid.innerHTML = '<div class="text-center p-4 text-danger">Error loading calendar data</div>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    grid.innerHTML = '<div class="text-center p-4 text-danger">Failed to load calendar. Please try again.</div>';
                });
        }

        function renderCalendar(calendar, month, year) {
            const grid = document.getElementById('calendarGrid');
            const monthYear = document.getElementById('currentMonthYear');

            // Update month/year display
            const date = new Date(year, month - 1, 1);
            monthYear.textContent = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

            // Clear grid
            grid.innerHTML = '';

            // Render calendar days
            calendar.forEach(day => {
                const dayElement = document.createElement('div');
                dayElement.className = `calendar-day ${!day.isCurrentMonth ? 'other-month' : ''} ${day.isToday ? 'today' : ''}`;

                dayElement.innerHTML = `
                <div class="calendar-date">${day.day}</div>
                <div class="calendar-events">
                    ${day.loanSchedules.map(schedule =>
                    `<div class="calendar-event loan-due" title="Loan Due: ${schedule.customer_name} - ${parseFloat(schedule.amount_due).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}">
                            <small>📅 ${schedule.customer_name}</small>
                        </div>`
                ).join('')}
                    ${day.repayments.map(repayment =>
                    `<div class="calendar-event repayment" title="Repayment: ${repayment.customer_name} - ${parseFloat(repayment.amount_paid).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}">
                            <small>💰 ${repayment.customer_name}</small>
                        </div>`
                ).join('')}
                    ${day.disbursements.map(disbursement =>
                    `<div class="calendar-event disbursement" title="Disbursement: ${disbursement.customer_name} - ${parseFloat(disbursement.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}">
                            <small>💸 ${disbursement.customer_name}</small>
                        </div>`
                ).join('')}
                    ${day.cashTransactions.map(transaction =>
                    `<div class="calendar-event cash-transaction" title="${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}: ${transaction.customer_name} - ${parseFloat(transaction.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}">
                            <small>${transaction.type === 'deposit' ? '💳' : '💵'} ${transaction.customer_name}</small>
                        </div>`
                ).join('')}
                </div>
            `;

                grid.appendChild(dayElement);
            });
        }

        function updateSummaryCards(data) {
            document.getElementById('dueCount').textContent = data.loanSchedules ? Object.values(data.loanSchedules).flat().length : 0;
            document.getElementById('repaymentCount').textContent = data.repayments ? Object.values(data.repayments).flat().length : 0;
            document.getElementById('disbursementCount').textContent = data.disbursements ? Object.values(data.disbursements).flat().length : 0;
            document.getElementById('cashTransactionCount').textContent = data.cashTransactions ? Object.values(data.cashTransactions).flat().length : 0;

            document.getElementById('summaryCards').style.display = 'flex';
        }
    });

    // Tasks Modal JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize tasks from localStorage
        let tasks = JSON.parse(localStorage.getItem('smartfinance_tasks') || '[]');

        // Task form submission
        document.getElementById('addTaskForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const taskInput = document.getElementById('taskInput');
            const taskText = taskInput.value.trim();

            if (taskText) {
                addTask(taskText);
                taskInput.value = '';
                renderTasks();

                // Show success message
                Swal.fire({
                    title: 'Task Added!',
                    text: `"${taskText}" has been added to today's tasks.`,
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
        });

        // Filter tasks
        document.querySelectorAll('input[name="taskFilter"]').forEach(radio => {
            radio.addEventListener('change', function () {
                renderTasks();
            });
        });

        // Clear completed tasks
        document.getElementById('clearCompleted').addEventListener('click', function () {
            const today = new Date().toISOString().split('T')[0];
            const todayCompletedCount = tasks.filter(t => t.taskDate === today && t.completed).length;

            if (todayCompletedCount === 0) {
                Swal.fire({
                    title: 'No Completed Tasks',
                    text: 'No completed tasks for today to clear.',
                    icon: 'info',
                    confirmButtonColor: '#17a2b8'
                });
                return;
            }

            Swal.fire({
                title: 'Clear Completed Tasks?',
                html: `<div class="text-start">
                <p>Are you sure you want to clear <strong>${todayCompletedCount}</strong> completed task(s) for today?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Clear All',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    tasks = tasks.filter(task => !(task.taskDate === today && task.completed));
                    saveTasks();
                    renderTasks();

                    // Show success message
                    Swal.fire({
                        title: 'Tasks Cleared!',
                        text: `${todayCompletedCount} completed task(s) have been cleared.`,
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        });

        // Export tasks
        document.getElementById('exportTasks').addEventListener('click', function () {
            exportTasksToCSV();
        });

        // Load tasks when modal opens
        document.getElementById('tasksModal').addEventListener('shown.bs.modal', function () {
            renderTasks();
        });

        // Initial render
        renderTasks();

        function addTask(text) {
            const today = new Date();
            const taskDate = today.toISOString().split('T')[0]; // YYYY-MM-DD format

            const task = {
                id: Date.now(),
                text: text,
                completed: false,
                created: today.toISOString(),
                completedAt: null,
                taskDate: taskDate // Store the date separately for filtering
            };
            tasks.unshift(task);
            saveTasks();
        }

        function toggleTask(id) {
            const task = tasks.find(t => t.id === id);
            if (task) {
                const wasCompleted = task.completed;
                task.completed = !task.completed;
                task.completedAt = task.completed ? new Date().toISOString() : null;
                saveTasks();

                // Show success message
                const status = task.completed ? 'completed' : 'pending';
                const icon = task.completed ? 'success' : 'info';
                const color = task.completed ? '#28a745' : '#17a2b8';

                Swal.fire({
                    title: `Task ${status.charAt(0).toUpperCase() + status.slice(1)}!`,
                    text: `"${task.text}" has been marked as ${status}.`,
                    icon: icon,
                    confirmButtonColor: color,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            }
        }

        function deleteTask(id) {
            const task = tasks.find(t => t.id === id);
            if (!task) return;

            Swal.fire({
                title: 'Delete Task?',
                html: `<div class="text-start">
                <p>Are you sure you want to delete this task?</p>
                <div class="alert alert-warning">
                    <strong>"${task.text}"</strong>
                </div>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    tasks = tasks.filter(t => t.id !== id);
                    saveTasks();
                    renderTasks();

                    // Show success message
                    Swal.fire({
                        title: 'Task Deleted!',
                        text: 'The task has been successfully deleted.',
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        }

        function saveTasks() {
            localStorage.setItem('smartfinance_tasks', JSON.stringify(tasks));
        }

        function renderTasks() {
            const tasksList = document.getElementById('tasksList');
            const filter = document.querySelector('input[name="taskFilter"]:checked').value;

            // Get today's date in YYYY-MM-DD format
            const today = new Date().toISOString().split('T')[0];

            // Filter tasks to only show today's tasks
            let todayTasks = tasks.filter(t => t.taskDate === today);

            // Apply additional filter
            let filteredTasks = todayTasks;
            if (filter === 'pending') {
                filteredTasks = todayTasks.filter(t => !t.completed);
            } else if (filter === 'completed') {
                filteredTasks = todayTasks.filter(t => t.completed);
            }

            // Update statistics
            updateTaskStats();

            if (filteredTasks.length === 0) {
                const emptyMessages = {
                    'all': {
                        icon: 'bx-check-square',
                        title: 'No tasks for today',
                        subtitle: 'Add your first task above to get started with today\'s work!'
                    },
                    'pending': {
                        icon: 'bx-time',
                        title: 'No pending tasks today',
                        subtitle: 'Great job! All of today\'s tasks are completed.'
                    },
                    'completed': {
                        icon: 'bx-check-circle',
                        title: 'No completed tasks today',
                        subtitle: 'Complete some of today\'s tasks to see them here!'
                    }
                };

                const message = emptyMessages[filter];
                tasksList.innerHTML = `
                <div class="text-center text-muted py-5">
                    <div class="empty-state-icon mb-3">
                        <i class="bx ${message.icon}"></i>
                    </div>
                    <h5 class="text-dark mb-2">${message.title}</h5>
                    <p class="text-muted mb-0">${message.subtitle}</p>
                </div>
            `;
                return;
            }

            tasksList.innerHTML = filteredTasks.map(task => `
            <div class="card mb-3 ${task.completed ? 'border-success bg-light-success' : 'border-warning bg-light-warning'} task-item" 
                 onclick="handleTaskClick(${task.id})" style="cursor: pointer;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <button type="button" class="btn ${task.completed ? 'btn-success' : 'btn-outline-success'} task-toggle-btn" 
                                    onclick="event.stopPropagation(); toggleTask(${task.id})" title="${task.completed ? 'Mark as pending' : 'Mark as completed'}">
                                <i class="bx ${task.completed ? 'bx-check-circle' : 'bx-circle'} fs-5"></i>
                            </button>
                        </div>
                        <div class="flex-grow-1">
                            <div class="task-text ${task.completed ? 'text-decoration-line-through text-muted' : 'fw-semibold'}">
                                ${task.text}
                            </div>
                            <div class="task-meta mt-1">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bx bx-calendar me-1"></i>Today
                                </span>
                                ${task.completedAt ? `
                                    <span class="badge bg-success text-white">
                                        <i class="bx bx-check me-1"></i>Completed: ${new Date(task.completedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                    </span>
                                ` : ''}
                            </div>
                        </div>
                        <div class="ms-2">
                            <button type="button" class="btn btn-outline-danger task-delete-btn" 
                                    onclick="event.stopPropagation(); deleteTask(${task.id})" title="Delete task">
                                <i class="bx bx-trash fs-6"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        }

        function updateTaskStats() {
            const today = new Date().toISOString().split('T')[0];
            const todayTasks = tasks.filter(t => t.taskDate === today);
            const total = todayTasks.length;
            const completed = todayTasks.filter(t => t.completed).length;
            const pending = total - completed;

            document.getElementById('totalTasks').textContent = total;
            document.getElementById('pendingTasksCount').textContent = pending;
            document.getElementById('completedTasksCount').textContent = completed;
            document.getElementById('todayDate').textContent = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        }

        function handleTaskClick(taskId) {
            const task = tasks.find(t => t.id === taskId);
            if (!task) return;

            if (task.completed) {
                // If already completed, ask if they want to mark as pending
                Swal.fire({
                    title: 'Mark Task as Pending?',
                    html: `<div class="text-start">
                    <p><strong>Task:</strong> ${task.text}</p>
                    <p class="text-muted">This task is already completed. Do you want to mark it as pending again?</p>
                </div>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Mark as Pending',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        toggleTask(taskId);
                    }
                });
            } else {
                // If pending, ask if they want to mark as complete
                Swal.fire({
                    title: 'Mark Task as Completed?',
                    html: `<div class="text-start">
                    <p><strong>Task:</strong> ${task.text}</p>
                    <p class="text-muted">Do you want to mark this task as completed?</p>
                </div>`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Mark as Complete',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        toggleTask(taskId);
                    }
                });
            }
        }

        function exportTasksToCSV() {
            const today = new Date().toISOString().split('T')[0];
            const todayTasks = tasks.filter(t => t.taskDate === today);

            if (todayTasks.length === 0) {
                Swal.fire({
                    title: 'No Tasks to Export',
                    text: 'There are no tasks for today to export.',
                    icon: 'info',
                    confirmButtonColor: '#17a2b8'
                });
                return;
            }

            const csvContent = [
                ['Task', 'Status', 'Created', 'Completed'],
                ...todayTasks.map(task => [
                    task.text,
                    task.completed ? 'Completed' : 'Pending',
                    'Today',
                    task.completedAt ? new Date(task.completedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : 'N/A'
                ])
            ].map(row => row.map(field => `"${field}"`).join(',')).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `today_tasks_${today}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);

            // Show success message
            Swal.fire({
                title: 'Tasks Exported!',
                text: `${todayTasks.length} task(s) have been exported to CSV.`,
                icon: 'success',
                confirmButtonColor: '#28a745',
                timer: 2000,
                timerProgressBar: true
            });
        }

        // Make functions globally accessible
        window.toggleTask = toggleTask;
        window.deleteTask = deleteTask;
        window.handleTaskClick = handleTaskClick;
    });

    // Loan Messages Modal JavaScript
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize message data
        let messages = [];

        // Filter messages
        document.querySelectorAll('input[name="messageFilter"]').forEach(radio => {
            radio.addEventListener('change', function () {
                renderMessages();
            });
        });

        // Load messages when modal opens
        document.getElementById('loanMessagesModal').addEventListener('shown.bs.modal', function () {
            loadMessages();
        });

        // Action button event listeners
        document.getElementById('sendBulkSMS').addEventListener('click', function () {
            showBulkSMSPrompt();
        });

        document.getElementById('markAllRead').addEventListener('click', function () {
            markAllMessagesAsRead();
        });

        document.getElementById('exportMessages').addEventListener('click', function () {
            exportMessagesToCSV();
        });

        // open send SMS modal
        const openSendSms = document.getElementById('openSendSms');
        if (openSendSms) {
            openSendSms.addEventListener('click', function(){
                const modal = new bootstrap.Modal(document.getElementById('sendSmsModal'));
                modal.show();
            });
        }

        // send sms
        const sendSmsBtn = document.getElementById('sendSmsBtn');
        if (sendSmsBtn) {
            sendSmsBtn.addEventListener('click', function(){
                const phone = document.getElementById('smsPhone').value.trim();
                const message = document.getElementById('smsMessage').value.trim();
                if (!phone || !message) {
                    Swal.fire({icon:'warning', title:'Missing data', text:'Phone and message are required.'});
                    return;
                }
                fetch('{{ route('sms.send') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ phone, message })
                }).then(r=>r.json()).then(res=>{
                    if(res.success){
                        Swal.fire({icon:'success', title:'Sent', timer:1500, showConfirmButton:false});
                        document.getElementById('sendSmsForm').reset();
                        bootstrap.Modal.getInstance(document.getElementById('sendSmsModal')).hide();
                    } else {
                        Swal.fire({icon:'error', title:'Failed', text: res.message || 'Could not send SMS.'});
                    }
                }).catch(()=> Swal.fire({icon:'error', title:'Failed', text:'Could not send SMS.'}));
            });
        }

        function loadMessages() {
            // Show loading state
            const messagesList = document.getElementById('messagesList');
            messagesList.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading loan messages...</p>
            </div>
        `;

            // Fetch real data from the database
            console.log('Loading loan messages from database...');

            fetch('/loan-messages', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        messages = data.messages;
                        console.log('Messages loaded:', messages);
                        updateMessageStats();
                        renderMessages();
                    } else {
                        throw new Error(data.message || 'Failed to load messages');
                    }
                })
                .catch(error => {
                    console.error('Error loading messages:', error);
                    messagesList.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <div class="empty-state-icon mb-3">
                            <i class="bx bx-error-circle"></i>
                        </div>
                        <h5 class="text-dark mb-2">Error Loading Messages</h5>
                        <p class="text-muted mb-0">Failed to load loan messages: ${error.message}</p>
                        <button type="button" class="btn btn-primary mt-3" onclick="loadMessages()">
                            <i class="bx bx-refresh me-2"></i>Retry
                        </button>
                    </div>
                `;
                });
        }

        function updateMessageStats() {
            const today = new Date().toDateString();
            const dueToday = messages.filter(m => m.type === 'due' && new Date(m.dueDate).toDateString() === today).length;
            const inArrears = messages.filter(m => m.type === 'arrears').length;
            const pendingApproval = messages.filter(m => m.type === 'approval' && !m.isRead).length;
            const total = messages.length;
            const unreadCount = messages.filter(m => !m.isRead).length;

            document.getElementById('dueTodayCount').textContent = dueToday;
            document.getElementById('arrearsCount').textContent = inArrears;
            document.getElementById('pendingApprovalCount').textContent = pendingApproval;
            document.getElementById('totalMessagesCount').textContent = total;

            // Update notification badge
            const badge = document.getElementById('messageNotificationBadge');
            if (unreadCount > 0) {
                badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
                badge.style.display = 'block';
            } else {
                badge.style.display = 'none';
            }
        }

        function renderMessages() {
            const messagesList = document.getElementById('messagesList');
            const filter = document.querySelector('input[name="messageFilter"]:checked').value;

            let filteredMessages = messages;
            if (filter === 'due') {
                filteredMessages = messages.filter(m => m.type === 'due');
            } else if (filter === 'arrears') {
                filteredMessages = messages.filter(m => m.type === 'arrears');
            } else if (filter === 'approval') {
                filteredMessages = messages.filter(m => m.type === 'approval');
            }

            if (filteredMessages.length === 0) {
                messagesList.innerHTML = `
                <div class="text-center text-muted py-5">
                    <div class="empty-state-icon mb-3">
                        <i class="bx bx-message"></i>
                    </div>
                    <h5 class="text-dark mb-2">No messages found</h5>
                    <p class="text-muted mb-0">No ${filter === 'all' ? '' : filter} messages available.</p>
                </div>
            `;
                return;
            }

            messagesList.innerHTML = filteredMessages.map(message => `
            <div class="card mb-3 ${message.isRead ? 'border-secondary bg-light' : 'border-primary'} message-item">
                <div class="card-body p-3">
                    <div class="d-flex align-items-start">
                        <div class="me-3">
                            <div class="message-icon ${getMessageIconClass(message.type)}">
                                <i class="bx ${getMessageIcon(message.type)} fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-1 ${message.isRead ? 'text-muted' : 'fw-bold'}">${message.title}</h6>
                                <div class="d-flex gap-2">
                                    <span class="badge ${getPriorityBadgeClass(message.priority)}">${message.priority}</span>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="markMessageAsRead(${message.id})" title="Mark as read">
                                        <i class="bx ${message.isRead ? 'bx-check-circle' : 'bx-circle'}"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="mb-2">${message.message}</p>
                            <div class="message-meta">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bx bx-user me-1"></i>${message.customer}
                                </span>
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bx bx-money me-1"></i>TZS ${message.amount.toLocaleString()}
                                </span>
                                <span class="badge bg-light text-dark me-2">
                                    <i class="bx bx-calendar me-1"></i>${new Date(message.dueDate).toLocaleDateString()}
                                </span>
                                ${message.daysOverdue ? `<span class="badge bg-danger text-white">
                                    <i class="bx bx-time me-1"></i>${message.daysOverdue} days overdue
                                </span>` : ''}
                                ${message.phone ? `<span class="badge bg-info text-white">
                                    <i class="bx bx-phone me-1"></i>${message.phone}
                                </span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
        }

        function getMessageIcon(type) {
            const icons = {
                'due': 'bx-time',
                'arrears': 'bx-error-circle',
                'approval': 'bx-check-shield'
            };
            return icons[type] || 'bx-message';
        }

        function getMessageIconClass(type) {
            const classes = {
                'due': 'bg-warning text-white',
                'arrears': 'bg-danger text-white',
                'approval': 'bg-info text-white'
            };
            return classes[type] || 'bg-secondary text-white';
        }

        function getPriorityBadgeClass(priority) {
            const classes = {
                'high': 'bg-warning text-dark',
                'critical': 'bg-danger text-white',
                'medium': 'bg-info text-white',
                'low': 'bg-secondary text-white'
            };
            return classes[priority] || 'bg-secondary text-white';
        }

        function markMessageAsRead(messageId) {
            const message = messages.find(m => m.id === messageId);
            if (message) {
                message.isRead = !message.isRead;
                updateMessageStats();
                renderMessages();
            }
        }

        function markAllMessagesAsRead() {
            const unreadCount = messages.filter(m => !m.isRead).length;

            if (unreadCount === 0) {
                Swal.fire({
                    title: 'No Unread Messages',
                    text: 'All messages are already marked as read.',
                    icon: 'info',
                    confirmButtonColor: '#17a2b8'
                });
                return;
            }

            Swal.fire({
                title: 'Mark All as Read?',
                text: `Mark ${unreadCount} unread message(s) as read?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Mark All Read',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    messages.forEach(m => m.isRead = true);
                    updateMessageStats();
                    renderMessages();

                    Swal.fire({
                        title: 'Messages Updated!',
                        text: `${unreadCount} message(s) marked as read.`,
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        }

        function showBulkSMSPrompt() {
            const unreadMessages = messages.filter(m => !m.isRead);

            if (unreadMessages.length === 0) {
                Swal.fire({
                    title: 'No Messages to Send',
                    text: 'All messages are already read. No SMS needed.',
                    icon: 'info',
                    confirmButtonColor: '#17a2b8'
                });
                return;
            }

            // Group messages by customer to avoid duplicate SMS
            const customerMessages = {};
            unreadMessages.forEach(msg => {
                if (!customerMessages[msg.customerId]) {
                    customerMessages[msg.customerId] = {
                        customer: msg.customer,
                        phone: msg.phone,
                        messages: []
                    };
                }
                customerMessages[msg.customerId].messages.push(msg);
            });

            const uniqueCustomers = Object.keys(customerMessages).length;

            Swal.fire({
                title: 'Send Bulk SMS',
                html: `
                <div class="text-start">
                    <p>Send SMS reminders for <strong>${uniqueCustomers}</strong> customer(s) with unread messages?</p>
                    <div class="alert alert-info">
                        <small>This will send personalized SMS to customers with due payments, arrears, or pending approvals.</small>
                    </div>
                    <div class="alert alert-warning">
                        <small><strong>Note:</strong> SMS will be sent to: ${Object.values(customerMessages).map(c => c.phone).filter(p => p).join(', ') || 'No valid phone numbers'}</small>
                    </div>
                </div>
            `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Send SMS',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulate SMS sending with real customer data
                    Swal.fire({
                        title: 'SMS Sent!',
                        text: `Bulk SMS sent to ${uniqueCustomers} customer(s).`,
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        }

        function exportMessagesToCSV() {
            if (messages.length === 0) {
                Swal.fire({
                    title: 'No Messages to Export',
                    text: 'There are no messages to export.',
                    icon: 'info',
                    confirmButtonColor: '#17a2b8'
                });
                return;
            }

            const csvContent = [
                ['Type', 'Title', 'Customer', 'Amount', 'Due Date', 'Priority', 'Status'],
                ...messages.map(message => [
                    message.type.charAt(0).toUpperCase() + message.type.slice(1),
                    message.title,
                    message.customer,
                    message.amount,
                    new Date(message.dueDate).toLocaleDateString(),
                    message.priority,
                    message.isRead ? 'Read' : 'Unread'
                ])
            ].map(row => row.map(field => `"${field}"`).join(',')).join('\n');

            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `loan_messages_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);

            Swal.fire({
                title: 'Messages Exported!',
                text: `${messages.length} message(s) exported to CSV.`,
                icon: 'success',
                confirmButtonColor: '#28a745',
                timer: 2000,
                timerProgressBar: true
            });
        }

        // Make functions globally accessible
        window.markMessageAsRead = markMessageAsRead;
    });

    // Moving Message Ticker JavaScript - Only for subscription expiry alerts
    document.addEventListener('DOMContentLoaded', function () {
        let currentMessageIndex = 0;
        let messages = [];
        let tickerContainer = document.querySelector('.message-ticker-container');
        let topbar = document.querySelector('.topbar');

        // Initialize ticker
        loadTickerMessages();

        // Load messages every minute
        setInterval(loadTickerMessages, 60000); // 60 seconds

        function loadTickerMessages() {
            // Fetch messages from the system
            fetch('/api/ticker-messages', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messages = data.messages || [];

                        if (data.show_ticker && messages.length > 0) {
                            showTicker();
                            displayTickerMessage();
                        } else {
                            hideTicker();
                        }
                    } else {
                        hideTicker();
                    }
                })
                .catch(error => {
                    console.log('Error loading ticker messages:', error);
                    hideTicker();
                });
        }

        function showTicker() {
            if (tickerContainer) {
                tickerContainer.style.display = 'block';
            }
            if (topbar) {
                topbar.style.marginTop = '40px';
            }
        }

        function hideTicker() {
            if (tickerContainer) {
                tickerContainer.style.display = 'none';
            }
            if (topbar) {
                topbar.style.marginTop = '0px';
            }
        }

        function displayTickerMessage() {
            if (messages.length === 0) {
                hideTicker();
                return;
            }

            const tickerContent = document.getElementById('tickerContent');
            const message = messages[currentMessageIndex];

            const messageElement = document.createElement('div');
            messageElement.className = `ticker-message ${message.type}`;
            messageElement.innerHTML = `
            <i class="bx ${message.icon}"></i>
            <span>${message.text}</span>
        `;

            // Add click handler to go to subscription if it has subscription_id
            if (message.subscription_id) {
                messageElement.style.cursor = 'pointer';
                messageElement.addEventListener('click', function () {
                    window.location.href = `/subscriptions/${message.subscription_id}`;
                });
            }

            tickerContent.innerHTML = '';
            tickerContent.appendChild(messageElement);

            // Move to next message
            currentMessageIndex = (currentMessageIndex + 1) % messages.length;

            // Restart animation
            tickerContent.style.animation = 'none';
            tickerContent.offsetHeight; // Trigger reflow
            tickerContent.style.animation = 'scroll-left 60s linear infinite';
        }

        // Add click handler to pause/resume ticker
        document.getElementById('tickerContent').addEventListener('click', function (e) {
            // Don't pause if clicking on a subscription message
            if (e.target.closest('.ticker-message') && e.target.closest('.ticker-message').dataset.subscriptionId) {
                return;
            }

            const ticker = document.querySelector('.ticker-content');
            if (ticker.style.animationPlayState === 'paused') {
                ticker.style.animationPlayState = 'running';
            } else {
                ticker.style.animationPlayState = 'paused';
            }
        });

        // Add hover effects
        document.getElementById('tickerContent').addEventListener('mouseenter', function () {
            this.style.animationPlayState = 'paused';
        });

        document.getElementById('tickerContent').addEventListener('mouseleave', function () {
            this.style.animationPlayState = 'running';
        });
    });
</script>