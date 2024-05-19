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

class LocalTransferOneTimeController extends Controller
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
    
        $data['receiver_card_number'] = preg_replace('/\s+/', '', $data['receiver_card_number']);
    
        $validatedData = Validator::make($data, [
            'bank_name' => 'required|string',
            'card_number' => 'required|string|exists:users,account_number',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'receiver_card_number' => 'required|string|exists:users,card_number',
            'money' => 'required|numeric|min:0.01',
            'purpose' => 'required|string|max:255',
        ], [
            'bank_name.required' => 'اسم البنك مطلوب.',
            'card_number.required' => 'رقم البطاقة مطلوب.',
            'card_number.exists' => 'رقم البطاقة غير موجود.',
            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'الاسم الأخير مطلوب.',
            'receiver_card_number.required' => 'رقم بطاقة المستلم مطلوب.',
            'receiver_card_number.exists' => 'رقم بطاقة المستلم غير موجود.',
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
    
        $sender = User::where('account_number', $data['card_number'])->first();
        $receiver = User::where('card_number', $data['receiver_card_number'])->first();
    
        if ($sender->total_money < $data['money']) {
            return response()->json(['message' => 'الرصيد غير كافي.'], 400);
        }
    
        function generateReferenceNumber()
        {
            return str_pad(random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        }
    
        // خصم المبلغ من المرسل
        $sender->total_money -= $data['money'];
        $sender->save();
    
        // إضافة المبلغ للمستلم
        $receiver->total_money += $data['money'];
        $receiver->save();
    
        // إنشاء سجل المعاملة
        $transaction = Transaction::create([
            'sender_account_number' => $sender->account_number,
            'receiver_account_number' => $receiver->account_number,
            'sender_card_number' => $sender->card_number,
            'amount' => $data['money'],
            'purpose' => $data['purpose'],
            'fee' => 0.58,
            'reference_number' => generateReferenceNumber(),
            'rajhi_benefits' => 'Local Bank beneficiary لمستفيد البنك المحلي' // Assuming you have this field
        ]);
    
        // إرسال الإشعارات
        $this->sendNotification($sender, $receiver, $data['money']);
    
        return response()->json([
            'status' => 200,
            'success' => true,
            'message' => 'تمت عملية التحويل بنجاح.',
            'transaction_details' => $transaction
        ]);
    }
    
    public function getLocalTransactionDetails(Request $request)
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

        $transactionDetails = $transaction->toArray();
        $transactionDetails['fee'] = (double)$transactionDetails['fee'];
        $receiver = User::where('account_number', $transaction->receiver_account_number)->first();

        return response()->json([
            'status' => 200,
            'success' => true,
            'transaction_details' => $transactionDetails,
            'receiver_name' => $receiver->name,
            'amount_available' => $receiver->total_money,
            'date' => $transaction->created_at->format('Y/m/d - h:i A'),
            'rajhi_beneficiary' => "Local Bank beneficiary لمستفيد البنك المحلي"
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
    public function checkReceiverCard(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'receiver_card_number' => 'required|string'
        ], [
            'receiver_card_number.required' => 'رقم البطاقة المستلمة مطلوب.',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['message' => $validatedData->errors()->first()], 400);
        }

        $receiver = User::where('card_number', $request->receiver_card_number)->first();

        if ($receiver) {
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'رقم البطاقة المستلمة موجود.',
                'receiver_name' => $receiver->name,
                'receiver_card_number' => $receiver->card_number
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'success' => false,
                'message' => 'رقم البطاقة المستلمة غير موجود.'
            ], 404);
        }
    }
}
