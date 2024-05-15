<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction; 

class ReceiptTransferController extends Controller
{
    public function showTransferReceipt($transactionId)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $sender = User::where('account_number', $transaction->sender_account_number)->first();
        $receiver = User::where('account_number', $transaction->receiver_account_number)->first();

        $data = [
            'date' => $transaction->created_at->format('Y/m/d - h:i A'),
            'amount' => $transaction->amount,
            'sender_name' => $sender->name,
            'sender_account_number' => $transaction->sender_account_number,
            'receiver_name' => $receiver->name,
            'receiver_account_number' => $transaction->receiver_account_number,
            'purpose' => $transaction->purpose,
            'transaction_id' => $transaction->id,
        ];

        return view('transfer_receipt', $data);
    }
}
