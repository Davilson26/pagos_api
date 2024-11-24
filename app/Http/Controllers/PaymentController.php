<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    private function getUserIdFromToken(Request $request)
    {
        $token = $request->header('authorization');
        $validation = $this->userService->validateSesion($token);

        if (!$validation['success']) {
            response()->json(['error' => 'Sesión no activa o token inválido'], 401)->send();
            exit; // Terminar ejecución si el token no es válido
        }

        return $validation;
    }

    public function processPayment(Request $request)
    {
        try {
            // Verificar usuario logueado
            $this->getUserIdFromToken($request);

            // Crear o recuperar un pago
            $payment = Payment::firstOrCreate([
                'card_number' => $request->payment_method['card_number'],
                'card_holder' => $request->payment_method['card_holder'],
                'card_type' => $request->payment_method['card_type'],
                'expiry_date' => $request->payment_method['expiry_date'],
                'cvv' => $request->payment_method['cvv'],
                'amount' => $request->total_amount,
                'status' => 1
            ]);

            $paymetId = $payment->id;
            $amount = $request->total_amount;
            $cardType = $request->payment_method['card_type'];

            // Calcular el descuento
            $discountAmount = $this->calculateDiscount($amount, $cardType);
            $finalAmount = $amount - $discountAmount;

            // Crear transacción
            $newTransaction = Transaction::create([
                'payment_id' => $paymetId,
                'amount' => $finalAmount,
                'original_amount' => $amount,
                'discount_applied' => $discountAmount,
                'status' => 1,
            ]);

            if ($newTransaction) {
                return response()->json([
                    'status' => 'success',
                    'transaction_id' => uniqid('txn_'),
                    'message' => 'Pago procesado con éxito',
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error al procesar pago',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
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
}
