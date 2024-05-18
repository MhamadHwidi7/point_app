<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class TransactionDetailsController extends Controller
{
    public function showTransactionDetails(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'reference_number' => 'required|string|exists:transactions,reference_number'
        ], [
            'reference_number.required' => 'رقم المرجع مطلوب.',
            'reference_number.exists' => 'رقم المرجع غير موجود.'
        ]);

        if ($validatedData->fails()) {
            return response()->json(['message' => $validatedData->errors()->first()], 400);
        }

        $transaction = Transaction::where('reference_number', $request->reference_number)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $sender = User::where('account_number', $transaction->sender_account_number)->first();
        $receiver = User::where('account_number', $transaction->receiver_account_number)->first();

        // Format card number
        $formattedCardNumber = 'SA** **** **** ****' . substr($sender->card_number, -4);

        return response()->json([
            'status' => 200,
            'success' => true,
            'amount' => number_format($transaction->amount, 2) . ' SAR',
            'from' => "{$sender->name}            \n{$formattedCardNumber}\nAl Rajhi Bank         ",
            'to' => $receiver->account_number,
            'purpose' => $transaction->purpose,
            'date' => $transaction->created_at->format('Y/m/d - h:i A'),
            'transaction_id' => $transaction->id,
            'rajhi_beneficiary' => $transaction->rajhi_benefits // Assuming you have this field in your transaction table
        ]);
    }
}
