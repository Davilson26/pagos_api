<?php
namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {

        // Validación de los datos del pago
        // $validator = Validator::make($request->all(), [
        //     'user_id' => 'required|integer',
        //     'total_amount' => 'required|numeric|min:0',
        //     'payment_method.card_number' => 'required|string',
        //     'payment_method.expiry_date' => 'required|date_format:m/y',
        //     'payment_method.card_type' => 'required|string|in:MasterCard,Visa',
        //     'payment_method.cvv' => 'required|string|min:3|max:4',
        //     'payment_method.cardholder_name' => 'required|string'
        // ]);

        // if ($validator->fails()) {
        //     return response()->json(['status' => 'error', 'errors' => $validator->errors()], 400);
        // }

        $cardData = Payment::firstOrCreate(
            [
            'card_number'=> $request->card_number ,
            'card_holder'=> $request->card_holder ,
            'expiry_date'=>  $request->expiry_date,
            'card_type'=>  $request->card_type,
            'cvv'=>  $request->cvv,
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
        $transactionSuccess = $this->simulatePaymentProcessing($request->payment_method, $finalAmount);

        $trasac = Transaction::create(
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
    private function simulatePaymentProcessing($paymentMethod, $finalAmount)
    {
        // Aquí puede ir la lógica de integración real con un proveedor de pagos.
        // Esta función simula que la transacción es exitosa para fines de prueba.
        return true; // Retorna true para indicar que el pago fue exitoso
    }
}
