<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class RajhiTransferOneTimeController extends Controller
{
    private $messaging;

    public function __construct(Factory $firebase)
    {
        $serviceAccountPath = storage_path('app/firebase/firebase_credentials.json');
        $this->messaging = $firebase->withServiceAccount($serviceAccountPath)->createMessaging();
    }

    public function transfer(Request $request)
    {
        $data = $request->all();

        $validatedData = Validator::make($data, [
            'sender_account_number' => 'required|string|exists:users,account_number',
            'receiver_account_number' => 'required|string|exists:users,account_number',
            'money' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
        ], [
            'sender_account_number.required' => 'رقم الحساب المرسل مطلوب.',
            'sender_account_number.exists' => 'رقم الحساب المرسل غير موجود.',
            'receiver_account_number.required' => 'رقم الحساب المستلم مطلوب.',
            'receiver_account_number.exists' => 'رقم الحساب المستلم غير موجود.',
            'money.required' => 'المبلغ مطلوب.',
            'money.numeric' => 'يجب أن يكون المبلغ رقمًا.',
            'money.min' => 'يجب أن يكون المبلغ أكبر من 0.',
            'purpose.required' => 'الغرض مطلوب.',
            'purpose.string' => 'يجب أن يكون الغرض نصيًا.',
            'purpose.max' => 'يجب ألا يتجاوز الغرض 255 حرفًا.'
        ]);

        if ($validatedData->fails()) {
            return response()->json(['message' => $validatedData->errors()->first()], 400);
        }

        $sender = User::where('account_number', $data['sender_account_number'])->first();
        $receiver = User::where('account_number', $data['receiver_account_number'])->first();

        if ($sender->total_money < $data['money']) {
            return response()->json(['message' => 'الرصيد غير كافي.'], 400);
        }

        function generateReferenceNumber()
        {
            return str_pad(random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        }

        // Deduct the amount from the sender
        $sender->total_money -= $data['money'];
        $sender->save();

        // Add the amount to the receiver
        $receiver->total_money += $data['money'];
        $receiver->save();

        // Create the transaction record
        $transaction = Transaction::create([
            'sender_account_number' => $sender->account_number,
            'receiver_account_number' => $receiver->account_number,
            'sender_card_number' => $sender->card_number,
            'amount' => $data['money'],
            'purpose' => $data['purpose'],
            'fee' => 0.58,
            'reference_number' => generateReferenceNumber(),
            'rajhi_benefits' => 'Al Rajhi Bank beneficiary لمستفيد بنك الراجحي' // Assuming you have this field
        ]);

        // Send notifications
        $this->sendNotification($sender, $receiver, $data['money']);

        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'تمت عملية التحويل بنجاح.',
            'transaction_details' => $transaction
        ]);
    }

    public function getTransactionDetails(Request $request)
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

        // Ensuring that fee is returned as a double
        $transactionDetails = $transaction->toArray();
        $transactionDetails['fee'] = (double)$transactionDetails['fee'];

        // Fetch the sender's name and the remaining balance
        $receiver = User::where('account_number', $transaction->receiver_account_number)->first();

        return response()->json([
            'status' => 200,
            'success' => true,
            'transaction_details' => $transactionDetails,
            'receiver_name' => $receiver->name,
            'amount_available' => $receiver->total_money,
            'date' => $transaction->created_at->format('Y/m/d - h:i A'),
            'rajhi_beneficiary' => "Al Rajhi Bank beneficiary لمستفيد بنك الراجحي"
        ]);
    }

    private function sendNotification(User $sender, User $receiver, float $amount)
    {
        $senderMessage = CloudMessage::withTarget('token', $sender->device_token)
            ->withNotification(Notification::create('تم التحويل بنجاح', "لقد قمت بتحويل مبلغ SAR $amount إلى {$receiver->name}."));

        $receiverMessage = CloudMessage::withTarget('token', $receiver->device_token)
            ->withNotification(Notification::create('تم استلام المال', "لقد استلمت مبلغ SAR $amount من {$sender->name}."));

        try {
            $this->messaging->send($senderMessage);
            $this->messaging->send($receiverMessage);
        } catch (\Throwable $e) {
            Log::error('فشل في إرسال الإشعار: ' . $e->getMessage());
        }
    }
}
