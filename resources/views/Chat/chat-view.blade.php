@extends('layouts.main')

@section('container')
<title>Messenger</title>


<div class="container-fluid"> 
    <div class="row chat-container">
        <!-- Left User Panel -->
        <div class="col-md-3 user-panel p-0">
            @forelse ($users as $user)
                <div class="user-item" id="user-{{ $user->id }}" onclick="selectUser({{ $user->id }}, '{{ $user->name }}')">
                    <div class="avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    <div>{{ $user->name }}</div>
                </div>
            @empty
                <p class="p-3">No users found.</p>
            @endforelse
        </div>

        <!-- Right Chat Panel -->
        <div class="col-md-9 p-0 chat-box">
            <div class="chat-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ url()->previous() }}" class="btn btn-light me-2" title="Back">
                        ←
                    </a>
                    <span id="chatHeader">Messenger</span>
                </div>
            </div>
            <div class="chat-body" id="chat-body">
                <div class="empty-chat">Select a user to start chatting</div>
            </div>
            <div class="chat-footer">
                <input type="text" id="messageInput" class="form-control" placeholder="Type your message..." />
                <button class="btn btn-send" onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>
</div>

{{-- Include your existing CSS and scripts --}}
<style>
    /* Your same styles, unchanged, below */
    .chat-container {
        height: calc(100vh - 120px);
        background-color: #f9fafb;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
    }
    .user-panel {
        background: #ffffff;
        height: 100%;
        overflow-y: auto;
        border-right: 1px solid #e6e6e6;
    }
    .user-item {
        padding: 14px 20px;
        cursor: pointer;
        transition: background 0.2s ease;
        border-bottom: 1px solid #f2f2f2;
        display: flex;
        align-items: center;
        font-weight: 500;
        color: #333;
    }
    .user-item:hover {
        background-color: #f4f4f4;
    }
    .user-item.active {
        background-color: #7f8fa6 ;
        color: #fff;
    }
    .user-item .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        margin-right: 12px;
        background-color: #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        color: #555;
    }
    .chat-box {
        display: flex;
        flex-direction: column;
        height: 100%;
        background-color: #fff;
    }
    .chat-header {
        padding: 16px 24px;
        background-color: #ffab40;
        color: #fff;
        font-size: 18px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
    }
    .chat-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background-color: #f8f9fa;
        position: relative;
    }
    .chat-footer {
        padding: 16px 24px;
        background-color: #fff;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
    }
    #messageInput {
        border-radius: 8px;
        height: 44px;
        border: 1px solid #dfe7f1;
        background: #f7f9fc;
        padding: 10px 14px;
    }
    .btn-send {
        background-color: #ffab40;
        border: none;
        color: white;
        font-weight: 600;
        padding: 0 20px;
        border-radius: 6px;
        height: 44px;
    }
    .btn-send:hover {
        background-color: #e63c5f;
    }
    .msg-bubble {
        padding: 10px 16px;
        border-radius: 16px;
        max-width: 75%;
        word-break: break-word;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
    }
    .msg-time {
        font-size: 11px;
        color: #fff;
        margin-top: 4px;
        text-align: right;
    }
    .msg-sender {
        font-weight: bold;
        margin-bottom: 2px;
    }
    .send_messages {
        justify-content: flex-end;
        display: flex;
        margin-bottom: 14px;
    }
    .send_messages .msg-bubble {
        background-color: #dfe4ea;
    }
    .received_messages {
        justify-content: flex-start;
        display: flex;
        flex-direction: column;
        margin-bottom: 14px;
    }
    .received_messages .msg-bubble {
        background-color: #6c8ebf;
        color: white;
    }
    .img-message {
        max-width: 250px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }
    .empty-chat {
        text-align: center;
        color: #aaa;
        margin-top: 100px;
        font-size: 16px;
    }
    @media (max-width: 768px) {
        .chat-container {
            flex-direction: column;
        }
        .user-panel {
            border-right: none;
            border-bottom: 1px solid #dee2e6;
        }
        .chat-box {
            border-left: none;
        }
    }
</style>

<script>
    let authUserId = "{{ Auth::user()->id }}";
    let userID = null;
    let userName = '';
    let newMessages = null;

    $('#messageInput').on('keypress', function (e) {
        if (e.which === 13) sendMessage();
    });

    function selectUser(userId, name = '') {
        $('.user-item').removeClass('active');
        $('#user-' + userId).addClass('active');
        $('#chat-body').empty();
        $('#chatHeader').text('Chat with ' + name);
        userID = userId;
        userName = name;
        getChatHistory();
        setupEventSource();
    }

    function getChatHistory() {
        $('#chat-body').empty();
        $.ajax({
            type: 'get',
            url: '{{ url('communication-history') }}',
            data: { userID: userID },
            success: function (data) {
                if (data.length === 0) {
                    $('#chat-body').html('<div class="empty-chat">No messages yet. Say hello!</div>');
                } else {
                    data.forEach(addMessageToBoard);
                }
            }
        });
    }

    function sendMessage() {
        const msg = $("#messageInput").val();
        if (!msg || !userID) return;
        $.ajax({
            type: 'post',
            url: '{{ route('send-message') }}',
            data: {
                '_token': "{{ csrf_token() }}",
                'message': msg,
                'user': userID,
            },
            success: function (data) {
                addMessageToBoard(data);
                $("#messageInput").val('');
            }
        });
    }

    function setupEventSource() {
        if (newMessages) newMessages.close();
        newMessages = new EventSource(`{{ url('/get-new-messages') }}/${userID}`);
        newMessages.onmessage = function (event) {
            let message = JSON.parse(event.data).item;
            addMessageToBoard(message);
        };
    }

    function addMessageToBoard(message) {
        const isSender = message.send_by === parseInt(authUserId);
        const wrapper = isSender ? 'send_messages' : 'received_messages';
        const senderLabel = isSender ? '' : `<div class="msg-sender">${userName}</div>`;
        const time = new Date(message.date_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const bubble = `
            <div class="${wrapper}">
                <div class="msg-bubble">
                    ${senderLabel}
                    ${checkMessageType(message)}
                    <div class="msg-time">${time}</div>
                </div>
            </div>`;
        $('#chat-body').append(bubble);
        $('#chat-body').scrollTop($('#chat-body')[0].scrollHeight);
    }

    function checkMessageType(message) {
        if (message.message_type === 'attachment') {
            return `<img src="{{ url('/') }}/${message.message}" class="img-message">`;
        }
        return message.message;
    }

    let selectedUserIdFromURL = "{{ $selectedUserId ?? '' }}";
    if (selectedUserIdFromURL) {
        setTimeout(() => {
            const el = document.getElementById('user-' + selectedUserIdFromURL);
            if (el) {
                el.click();
            }
        }, 100);
    }
</script>
@endsection
