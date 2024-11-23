<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    private function getUserIdFromToken(Request $request)
    {
        $token = $request->header('Authorization');
        $validation = $this->userService->validateSesion($token);

        if (!$validation['success']) {
            response()->json(['error' => 'Sesión no activa o token inválido'], 401)->send();
            exit; // Terminar ejecución si el token no es válido
        }

        return $validation;
    }

    public function processPayment(Request $request)
    {
        //verificar usuario logueado
        $this->getUserIdFromToken($request);

        //Validación de los datos del pago
        $validator = Validator::make($request->all(), [
            'total_amount' => 'required|numeric|min:0',
            'payment_method.card_number' => 'required|string',
            'payment_method.expiry_date' => 'required|date_format:m/y',
            'payment_method.card_type' => 'required|string|in:MasterCard,Visa',
            'payment_method.cvv' => 'required|string|min:3|max:4',
            'payment_method.cardholder_name' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
        }


        $cardData = Payment::firstOrCreate(
            [
                'card_number' => $request->card_number,
                'card_holder' => $request->card_holder,
                'expiry_date' =>  $request->expiry_date,
                'card_type' =>  $request->card_type,
                'cvv' =>  $request->cvv,
                'amount' => $request->amount
            ]
        );

        return $cardData;

        // Obtener el monto y el tipo de tarjeta
        $amount = $request->total_amount;
        $cardType = $request->payment_method['card_type'];

        // Calcular el descuento según el tipo de tarjeta
        $discountAmount = $this->calculateDiscount($amount, $cardType);
        $finalAmount = $amount - $discountAmount;

        // Simulación de la lógica de procesamiento de pago
        $transactionSuccess = $this->simulatePaymentProcessing($request->payment_method);

        // crear transacción
        Transaction::create(
            [
                'original_amount' => $amount,
                'discount_applied' => $discountAmount,
                'final_amount' => $finalAmount,
                'status' => 1,
            ]
        );


        if ($transactionSuccess) {
            // Respuesta exitosa
            return response()->json([
                'status' => 'success',
                'transaction_id' => uniqid('txn_'), // Generación de un ID único para la transacción

                'message' => 'Pago procesado con éxito'
            ], 200);
        } else {
            // Respuesta de error
            return response()->json([
                'status' => 'error',
                'error' => 'Hubo un problema al procesar el pago'
            ], 500);
        }
    }

    /**
     * Calcula el descuento basado en el tipo de tarjeta
     */
    private function calculateDiscount($amount, $cardType)
    {
        switch ($cardType) {
            case 'MasterCard':
                return $amount * 0.15;
            case 'Visa':
                return $amount * 0.10;
            default:
                return 0;
        }
    }

    /**
     * Simula el procesamiento del pago
     */
    private function simulatePaymentProcessing($paymentMethod)
    {
        $validator = Validator::make($paymentMethod, [
            'card_number' => 'required|string',
            'expiry_date' => 'required|date_format:m/y',
            'card_type' => 'required|string|in:MasterCard,Visa',
            'cvv' => 'required|string|min:3|max:4',
            'card_holder' => 'required|string'
        ]);

        if ($validator->fails()) {
            return false; // Retorna falso si la validación falla
        }

        // Lógica simulada de pago
        return true; // Suponiendo que el pago fue exitoso
    }
}
