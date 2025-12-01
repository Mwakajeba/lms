@extends('layouts.main')

@section('title', 'Chat')

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-breadcrumb d-flex align-items-center">
                    <div class="me-auto">
                        <x-breadcrumbs-with-icons :links="[
                            ['label' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'bx bx-home'],
                            ['label' => 'Chat', 'url' => '#', 'icon' => 'bx bx-message-rounded-dots']
                        ]" />
                    </div>
                </div>
            </div>
        </div>
        <h6 class="mb-0 text-uppercase">CHAT</h6>
        <hr />

        <!-- Chat Container -->
        <div class="row">
            <div class="col-12">
                <div class="card radius-10">
                    <div class="card-body p-0">
                        <div class="chat-wrapper">
                            <div class="chat-sidebar">
                                <div class="chat-sidebar-header">
                                    <div class="d-flex align-items-center">
                                        <div class="chat-user-online">
                                            <img src="assets/images/avatars/avatar-1.png" width="45" height="45" class="rounded-circle" alt="" />
                                        </div>
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0">Rachel Zane</p>
                                        </div>
                                        <div class="dropdown">
                                            <div class="cursor-pointer font-24 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown"><i class='bx bx-dots-horizontal-rounded'></i>
                                            </div>
                                            <div class="dropdown-menu dropdown-menu-end"> <a class="dropdown-item" href="javascript:;">Settings</a>
                                                <div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Help & Feedback</a>
                                                <a class="dropdown-item" href="javascript:;">Enable Split View Mode</a>
                                                <a class="dropdown-item" href="javascript:;">Keyboard Shortcuts</a>
                                                <div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Sign Out</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3"></div>
                                    <div class="input-group input-group-sm"> <span class="input-group-text bg-transparent"><i class='bx bx-search'></i></span>
                                        <input type="text" class="form-control" placeholder="People, groups, & messages"> <span class="input-group-text bg-transparent"><i class='bx bx-dialpad'></i></span>
                                    </div>
                                    <div class="chat-tab-menu mt-3">
                                        <ul class="nav nav-pills nav-justified">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="pill" href="javascript:;">
                                                    <div class="font-24"><i class='bx bx-conversation'></i>
                                                    </div>
                                                    <div><small>Chats</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="pill" href="javascript:;">
                                                    <div class="font-24"><i class='bx bx-phone'></i>
                                                    </div>
                                                    <div><small>Calls</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="pill" href="javascript:;">
                                                    <div class="font-24"><i class='bx bxs-contact'></i>
                                                    </div>
                                                    <div><small>Contacts</small>
                                                    </div>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="pill" href="javascript:;">
                                                    <div class="font-24"><i class='bx bx-bell'></i>
                                                    </div>
                                                    <div><small>Notifications</small>
                                                    </div>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="chat-sidebar-content">
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="tab-pane fade show active" id="pills-Chats">
                                            <div class="p-3">
                                                <div class="meeting-button d-flex justify-content-between">
                                                    <div class="dropdown"> <a href="#" class="btn btn-white btn-sm radius-30 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown"><i class='bx bx-video me-2'></i>Meet Now<i class='bx bxs-chevron-down ms-2'></i></a>
                                                        <div class="dropdown-menu"> <a class="dropdown-item" href="#">Host a meeting</a>
                                                            <a class="dropdown-item" href="#">Join a meeting</a>
                                                        </div>
                                                    </div>
                                                    <div class="dropdown"> <a href="#" class="btn btn-white btn-sm radius-30 dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown" data-display="static"><i class='bx bxs-edit me-2'></i>New Chat<i class='bx bxs-chevron-down ms-2'></i></a>
                                                        <div class="dropdown-menu dropdown-menu-right">	<a class="dropdown-item" href="#">New Group Chat</a>
                                                            <a class="dropdown-item" href="#">New Moderated Group</a>
                                                            <a class="dropdown-item" href="#">New Chat</a>
                                                            <a class="dropdown-item" href="#">New Private Conversation</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="dropdown mt-3"> <a href="#" class="text-uppercase text-secondary dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown">Recent Chats <i class='bx bxs-chevron-down'></i></a>
                                                    <div class="dropdown-menu">	<a class="dropdown-item" href="#">Recent Chats</a>
                                                        <a class="dropdown-item" href="#">Hidden Chats</a>
                                                        <div class="dropdown-divider"></div>	<a class="dropdown-item" href="#">Sort by Time</a>
                                                        <a class="dropdown-item" href="#">Sort by Unread</a>
                                                        <div class="dropdown-divider"></div>	<a class="dropdown-item" href="#">Show Favorites</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="chat-list">
                                                <div class="list-group list-group-flush">
                                                    <a href="javascript:;" class="list-group-item">
                                                        <div class="d-flex">
                                                            <div class="chat-user-online">
                                                                <img src="assets/images/avatars/avatar-2.png" width="42" height="42" class="rounded-circle" alt="" />
                                                            </div>
                                                            <div class="flex-grow-1 ms-2">
                                                                <h6 class="mb-0 chat-title">Louis Litt</h6>
                                                                <p class="mb-0 chat-msg">You just got LITT up, Mike.</p>
                                                            </div>
                                                            <div class="chat-time">9:51 AM</div>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:;" class="list-group-item active">
                                                        <div class="d-flex">
                                                            <div class="chat-user-online">
                                                                <img src="assets/images/avatars/avatar-3.png" width="42" height="42" class="rounded-circle" alt="" />
                                                            </div>
                                                            <div class="flex-grow-1 ms-2">
                                                                <h6 class="mb-0 chat-title">Harvey Specter</h6>
                                                                <p class="mb-0 chat-msg">Wrong. You take the gun....</p>
                                                            </div>
                                                            <div class="chat-time">4:32 PM</div>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:;" class="list-group-item">
                                                        <div class="d-flex">
                                                            <div class="chat-user-online">
                                                                <img src="assets/images/avatars/avatar-4.png" width="42" height="42" class="rounded-circle" alt="" />
                                                            </div>
                                                            <div class="flex-grow-1 ms-2">
                                                                <h6 class="mb-0 chat-title">Rachel Zane</h6>
                                                                <p class="mb-0 chat-msg">I was thinking that we could...</p>
                                                            </div>
                                                            <div class="chat-time">Wed</div>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:;" class="list-group-item">
                                                        <div class="d-flex">
                                                            <div class="chat-user-online">
                                                                <img src="assets/images/avatars/avatar-5.png" width="42" height="42" class="rounded-circle" alt="" />
                                                            </div>
                                                            <div class="flex-grow-1 ms-2">
                                                                <h6 class="mb-0 chat-title">Donna Paulsen</h6>
                                                                <p class="mb-0 chat-msg">Mike, I know everything!</p>
                                                            </div>
                                                            <div class="chat-time">Tue</div>
                                                        </div>
                                                    </a>
                                                    <a href="javascript:;" class="list-group-item">
                                                        <div class="d-flex">
                                                            <div class="chat-user-online">
                                                                <img src="assets/images/avatars/avatar-6.png" width="42" height="42" class="rounded-circle" alt="" />
                                                            </div>
                                                            <div class="flex-grow-1 ms-2">
                                                                <h6 class="mb-0 chat-title">Jessica Pearson</h6>
                                                                <p class="mb-0 chat-msg">Have you finished the draft...</p>
                                                            </div>
                                                            <div class="chat-time">9/3/2020</div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-header d-flex align-items-center">
                                <div class="chat-toggle-btn"><i class='bx bx-menu-alt-left'></i>
                                </div>
                                <div>
                                    <h4 class="mb-1 font-weight-bold">Harvey Inspector</h4>
                                    <div class="list-inline d-sm-flex mb-0 d-none"> <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary"><small class='bx bxs-circle me-1 chart-online'></small>Active Now</a>
                                        <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary">|</a>
                                        <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary"><i class='bx bx-images me-1'></i>Gallery</a>
                                        <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary">|</a>
                                        <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary"><i class='bx bx-search me-1'></i>Find</a>
                                    </div>
                                </div>
                                <div class="chat-top-header-menu ms-auto"> <a href="javascript:;"><i class='bx bx-video'></i></a>
                                    <a href="javascript:;"><i class='bx bx-phone'></i></a>
                                    <a href="javascript:;"><i class='bx bx-user-plus'></i></a>
                                </div>
                            </div>
                            <div class="chat-content">
                                <div class="chat-content-leftside">
                                    <div class="d-flex">
                                        <img src="assets/images/avatars/avatar-3.png" width="48" height="48" class="rounded-circle" alt="" />
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 chat-time">Harvey, 2:35 PM</p>
                                            <p class="chat-left-msg">Hi, harvey where are you now a days?</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-rightside">
                                    <div class="d-flex ms-auto">
                                        <div class="flex-grow-1 me-2">
                                            <p class="mb-0 chat-time text-end">you, 2:37 PM</p>
                                            <p class="chat-right-msg">I am in USA</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-leftside">
                                    <div class="d-flex">
                                        <img src="assets/images/avatars/avatar-3.png" width="48" height="48" class="rounded-circle" alt="" />
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 chat-time">Harvey, 2:48 PM</p>
                                            <p class="chat-left-msg">okk, what about admin template?</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-rightside">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 me-2">
                                            <p class="mb-0 chat-time text-end">you, 2:49 PM</p>
                                            <p class="chat-right-msg">i have already purchased the admin template</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-leftside">
                                    <div class="d-flex">
                                        <img src="assets/images/avatars/avatar-3.png" width="48" height="48" class="rounded-circle" alt="" />
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 chat-time">Harvey, 3:12 PM</p>
                                            <p class="chat-left-msg">ohhk, great, which admin template you have purchased?</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-rightside">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 me-2">
                                            <p class="mb-0 chat-time text-end">you, 3:14 PM</p>
                                            <p class="chat-right-msg">i purchased dashtreme admin template from themeforest. it is very good product for web application</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-leftside">
                                    <div class="d-flex">
                                        <img src="assets/images/avatars/avatar-3.png" width="48" height="48" class="rounded-circle" alt="" />
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 chat-time">Harvey, 3:16 PM</p>
                                            <p class="chat-left-msg">who is the author of this template?</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-rightside">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 me-2">
                                            <p class="mb-0 chat-time text-end">you, 3:22 PM</p>
                                            <p class="chat-right-msg">codervent is the author of this admin template</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-leftside">
                                    <div class="d-flex">
                                        <img src="assets/images/avatars/avatar-3.png" width="48" height="48" class="rounded-circle" alt="" />
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 chat-time">Harvey, 3:16 PM</p>
                                            <p class="chat-left-msg">ohh i know about this author. he has good admin products in his portfolio.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-rightside">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 me-2">
                                            <p class="mb-0 chat-time text-end">you, 3:30 PM</p>
                                            <p class="chat-right-msg">yes, codervent has multiple admin templates. also he is very supportive.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-leftside">
                                    <div class="d-flex">
                                        <img src="assets/images/avatars/avatar-3.png" width="48" height="48" class="rounded-circle" alt="" />
                                        <div class="flex-grow-1 ms-2">
                                            <p class="mb-0 chat-time">Harvey, 3:33 PM</p>
                                            <p class="chat-left-msg">All the best for your target. thanks for giving your time.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="chat-content-rightside">
                                    <div class="d-flex">
                                        <div class="flex-grow-1 me-2">
                                            <p class="mb-0 chat-time text-end">you, 3:35 PM</p>
                                            <p class="chat-right-msg">thanks Harvey</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="chat-footer d-flex align-items-center">
                                <div class="flex-grow-1 pe-2">
                                    <div class="input-group">	<span class="input-group-text"><i class='bx bx-smile'></i></span>
                                        <input type="text" class="form-control" id="messageInput" placeholder="Type a message">
                                        <button class="btn btn-primary" type="button" id="sendMessageBtn">
                                            <i class='bx bx-send'></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="chat-footer-menu"> <a href="javascript:;"><i class='bx bx-file'></i></a>
                                    <a href="javascript:;"><i class='bx bxs-contact'></i></a>
                                    <a href="javascript:;"><i class='bx bx-microphone'></i></a>
                                    <a href="javascript:;"><i class='bx bx-dots-horizontal-rounded'></i></a>
                                </div>
                            </div>
                            <!--start chat overlay-->
                            <div class="overlay chat-toggle-btn-mobile"></div>
                            <!--end chat overlay-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.chat-wrapper {
    display: flex;
    height: calc(100vh - 300px);
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    min-height: 600px;
}

.chat-sidebar {
    width: 320px;
    border-right: 1px solid #e9ecef;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
}

.chat-sidebar-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e9ecef;
    background: #fff;
}

.chat-sidebar-content {
    flex: 1;
    overflow-y: auto;
}

.chat-list {
    padding: 0;
}

.chat-list .list-group-item {
    border: none;
    border-bottom: 1px solid #f1f3f4;
    padding: 1rem 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chat-list .list-group-item:hover {
    background: #e3f2fd;
}

.chat-list .list-group-item.active {
    background: #bbdefb;
    border-left: 4px solid #2196f3;
}

.chat-user-online {
    position: relative;
    margin-right: 1rem;
}

.chat-user-online img {
    width: 42px;
    height: 42px;
    object-fit: cover;
}

.chat-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: #333;
    margin: 0;
}

.chat-msg {
    font-size: 0.8rem;
    color: #666;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chat-time {
    font-size: 0.75rem;
    color: #999;
    margin: 0;
}

.chat-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
    background: #fff;
}

.chat-toggle-btn {
    cursor: pointer;
    margin-right: 1rem;
}

.chat-top-header-menu a {
    color: #666;
    margin-left: 1rem;
    text-decoration: none;
}

.chat-top-header-menu a:hover {
    color: #2196f3;
}

.chat-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f5f5f5;
    overflow-y: auto;
    padding: 1.5rem;
}

.chat-content-leftside,
.chat-content-rightside {
    margin-bottom: 1rem;
}

.chat-content-leftside .d-flex {
    align-items: flex-end;
}

.chat-content-rightside .d-flex {
    align-items: flex-end;
    justify-content: flex-end;
}

.chat-left-msg,
.chat-right-msg {
    background: white;
    padding: 0.75rem 1rem;
    border-radius: 18px;
    border-bottom-left-radius: 4px;
    margin: 0;
    max-width: 70%;
    word-wrap: break-word;
}

.chat-right-msg {
    background: #2196f3;
    color: white;
    border-bottom-right-radius: 4px;
    border-bottom-left-radius: 18px;
}

.chat-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e9ecef;
    background: #fff;
}

.chat-footer .input-group {
    border-radius: 25px;
    overflow: hidden;
}

.chat-footer .input-group .form-control {
    border: none;
    padding: 0.75rem 1rem;
}

.chat-footer .input-group .btn {
    border: none;
    padding: 0.75rem 1rem;
    border-radius: 0 25px 25px 0;
}

.chat-footer .input-group .btn-primary {
    background: #2196f3;
}

.chat-footer .input-group .btn-primary:hover {
    background: #1976d2;
}

.chat-footer-menu a {
    color: #666;
    margin-left: 1rem;
    text-decoration: none;
}

.chat-footer-menu a:hover {
    color: #2196f3;
}

.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

/* Responsive Design */
@media (max-width: 768px) {
    .chat-wrapper {
        height: calc(100vh - 350px);
        min-height: 500px;
    }
    
    .chat-sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
    
    .chat-sidebar.show {
        transform: translateX(0);
    }
    
    .overlay.show {
        display: block;
    }
}

/* Scrollbar Styling */
.chat-sidebar-content::-webkit-scrollbar,
.chat-content::-webkit-scrollbar {
    width: 6px;
}

.chat-sidebar-content::-webkit-scrollbar-track,
.chat-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-sidebar-content::-webkit-scrollbar-thumb,
.chat-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chat-sidebar-content::-webkit-scrollbar-thumb:hover,
.chat-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Send message functionality
    $('#sendMessageBtn').on('click', function() {
        sendMessage();
    });

    $('#messageInput').on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function sendMessage() {
        const message = $('#messageInput').val().trim();
        if (!message) return;

        // Add message to chat
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        const messageHtml = `
            <div class="chat-content-rightside">
                <div class="d-flex">
                    <div class="flex-grow-1 me-2">
                        <p class="mb-0 chat-time text-end">you, ${time}</p>
                        <p class="chat-right-msg">${escapeHtml(message)}</p>
                    </div>
                </div>
            </div>
        `;
        
        $('.chat-content').append(messageHtml);
        
        // Clear input
        $('#messageInput').val('');
        
        // Scroll to bottom
        $('.chat-content').scrollTop($('.chat-content')[0].scrollHeight);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
