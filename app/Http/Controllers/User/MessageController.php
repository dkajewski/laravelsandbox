<?php

namespace App\Http\Controllers\User;

use App\Events\MessageEvent;
use App\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $user = $request->user();
        if (!empty($request->receiverId) && $user->id === $request->receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'basic.message-cant-be-send-to-yourself'
            ]);
        }

        if (!empty($request->message) && !trim($request->message)) {
            return response()->json([
                'success' => false,
                'message' => 'basic.message-cant-be-empty'
            ]);
        }

        $message = new Message();
        $message->sender_id = $user->id;
        $message->receiver_id = $request->receiverId;
        $message->message = $request->message;
        $message->save();
        $messageToReturn = [
            'created_at' => $message->created_at,
            'id' => $message->id,
            'message' => $message->message,
            'receiver_id' => $message->receiver_id,
            'receiver_read' => $message->receiver_read,
            'sender_id' => $message->sender_id,
            'to' => $message->sender_id,
        ];
        broadcast(new MessageEvent($request->receiverId, $messageToReturn));

        return response()->json([
            'success' => true,
            'message' => 'basic.message-sent',
            'data' => $messageToReturn,
        ]);
    }

    public function fetchMessages(Request $request)
    {
        $user = $request->user();
        $chatWith = $request->chatWith;
        $receivedMessages = $user->receivedMessages->where('sender_id', '=', $chatWith);
        $sentMessages = $user->sentMessages->where('receiver_id', '=', $chatWith);
        $messages = $receivedMessages->merge($sentMessages);
        $fields = ['id', 'message', 'sender_id', 'receiver_id', 'created_at'];
        $messages = $messages->map(function($message) use ($fields) {
            return $message->only($fields);
        });

        return response()->json([
           'messages' => $messages,
        ]);
    }

    public function fetchConversations(Request $request)
    {
        $user = $request->user();
        $receivedMessages = $user->receivedMessages->sortByDesc('created_at')->unique('sender_id');
        $sentMessages = $user->sentMessages->sortByDesc('created_at')->unique('receiver_id');
        foreach ($receivedMessages as $key => $receivedMessage) {
            $sentMessages = $sentMessages->reject(function ($sentMessage) use ($receivedMessage) {
                return $this->isReceivedMessageNewerThanSentToTheSameUser($sentMessage, $receivedMessage);
            });
        }

        foreach ($sentMessages as $sentMessage) {
            $receivedMessages = $receivedMessages->reject(function ($receivedMessage) use ($sentMessage) {
                return $receivedMessage->sender_id === $sentMessage->receiver_id;
            });
        }

       $conversations = $sentMessages->merge($receivedMessages)->sortByDesc('created_at');
        foreach ($conversations as &$conversation) {
            if ($user->id === $conversation->sender->id) {
                $conversation->avatar = $conversation->receiver->avatar ? 'images/avatars/'.$conversation->receiver->id.'/'.$conversation->receiver->avatar : '';
                $conversation->name = $conversation->receiver->name;
                $conversation->to = $conversation->receiver->id;
            } else {
                $conversation->avatar = $conversation->sender->avatar ? 'images/avatars/'.$conversation->sender->id.'/'.$conversation->sender->avatar : '';
                $conversation->name = $conversation->sender->name;
                $conversation->to = $conversation->sender->id;
            }
        }

        $fields = ['id', 'receiver_id', 'sender_id', 'message', 'receiver_read', 'created_at', 'avatar', 'name', 'to'];
        $conversations = $conversations->map(function ($conversation) use ($fields) {
            return $conversation->only($fields);
        });

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    public function markAsRead(Request $request)
    {
        $user = $request->user();
        Message::where([
            ['receiver_id', '=', $user->id],
            ['sender_id', '=', $request->senderId],
            ['receiver_read', '=', 0]
        ])->update(['receiver_read' => 1]);

        return response()->json([
            'success' => true,
        ]);
    }

    private function isReceivedMessageNewerThanSentToTheSameUser($sentMessage, $receivedMessage)
    {
        if (
            $sentMessage->receiver_id !== $receivedMessage->sender_id
            && $sentMessage->sender_id !== $receivedMessage->receiver_id
        ) {
            return false;
        }

        if ($receivedMessage->created_at > $sentMessage->created_at) {
            return true;
        }

        return false;
    }
}
