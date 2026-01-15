<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Sale;
use App\Models\LoanPayment;
use Illuminate\Support\Facades\Auth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

class LoanPaymentApiTest extends TestCase
{
    protected $user;
    protected $sale;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a test tenant and assign to user
        $tenant = \App\Models\Tenant::factory()->create();
        $this->user->tenant_id = $tenant->id;
        $this->user->save();

        // Create a test customer and sale
        $customer = \App\Models\Customer::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $this->sale = Sale::factory()->create([
            'tenant_id' => $tenant->id,
            'customer_id' => $customer->id,
            'total_amount' => 5000.00,
        ]);

        // Authenticate the user
        Auth::login($this->user);

        // Set up headers for API requests
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->user->createToken('test-token')->plainTextToken,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /** @test */
    public function can_get_all_loan_payments()
    {
        // Create some loan payments
        LoanPayment::factory()->count(3)->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get('/api/loan-payments', $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'sale_id',
                            'amount',
                            'payment_date',
                            'notes',
                            'user_id',
                            'sale' => [
                                'customer' => [
                                    'name'
                                ]
                            ],
                            'user' => [
                                'name'
                            ],
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'total'
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(3, count($response->json('data.data')));
    }

    /** @test */
    public function can_create_loan_payment()
    {
        $paymentData = [
            'sale_id' => $this->sale->id,
            'amount' => 1500.00,
            'payment_date' => now()->toDateString(),
            'notes' => 'Test payment',
        ];

        $response = $this->post('/api/loan-payments', $paymentData, $this->headers);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'sale_id',
                    'amount',
                    'payment_date',
                    'notes',
                    'user_id',
                    'created_at',
                    'updated_at'
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(1500.00, $response->json('data.amount'));
        $this->assertEquals($this->user->id, $response->json('data.user_id'));

        // Verify the payment was saved in database
        $this->assertDatabaseHas('loan_payments', [
            'sale_id' => $this->sale->id,
            'amount' => 1500.00,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function can_get_specific_loan_payment()
    {
        $payment = LoanPayment::factory()->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->get("/api/loan-payments/{$payment->id}", $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'sale_id',
                    'amount',
                    'payment_date',
                    'notes',
                    'user_id',
                    'sale' => [
                        'customer' => [
                            'name'
                        ]
                    ],
                    'user' => [
                        'name'
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals($payment->id, $response->json('data.id'));
    }

    /** @test */
    public function can_update_loan_payment()
    {
        $payment = LoanPayment::factory()->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
            'amount' => 1000.00,
        ]);

        $updateData = [
            'amount' => 2000.00,
            'notes' => 'Updated payment',
        ];

        $response = $this->put("/api/loan-payments/{$payment->id}", $updateData, $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'amount',
                    'notes',
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(2000.00, $response->json('data.amount'));
        $this->assertEquals('Updated payment', $response->json('data.notes'));

        // Verify the update in database
        $this->assertDatabaseHas('loan_payments', [
            'id' => $payment->id,
            'amount' => 2000.00,
            'notes' => 'Updated payment',
        ]);
    }

    /** @test */
    public function can_delete_loan_payment()
    {
        $payment = LoanPayment::factory()->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->delete("/api/loan-payments/{$payment->id}", [], $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message'
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Loan payment deleted successfully', $response->json('message'));

        // Verify the payment was deleted from database
        $this->assertDatabaseMissing('loan_payments', [
            'id' => $payment->id,
        ]);
    }

    /** @test */
    public function can_get_loan_payment_statistics()
    {
        // Create some payments
        LoanPayment::factory()->count(5)->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'payment_date' => now()->subMonth(),
        ]);

        LoanPayment::factory()->count(3)->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
            'amount' => 500.00,
            'payment_date' => now(),
        ]);

        $response = $this->get('/api/loan-payments/statistics', $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'total_payments',
                    'total_amount',
                    'average_payment',
                    'payments_this_month',
                    'amount_this_month',
                    'daily_average'
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(8, $response->json('data.total_payments'));
        $this->assertEquals(6500.00, $response->json('data.total_amount')); // 5*1000 + 3*500
    }

    /** @test */
    public function can_get_payments_by_sale()
    {
        // Create payments for the sale
        LoanPayment::factory()->count(3)->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
            'amount' => 1000.00,
        ]);

        $response = $this->get("/api/loan-payments/sale/{$this->sale->id}", $this->headers);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'payments' => [
                        '*' => [
                            'id',
                            'amount',
                            'payment_date',
                            'notes',
                            'user' => [
                                'name'
                            ],
                            'created_at'
                        ]
                    ],
                    'sale_info' => [
                        'id',
                        'customer',
                        'total_amount',
                        'total_paid',
                        'remaining_balance',
                        'payment_status'
                    ]
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals(3, count($response->json('data.payments')));
        $this->assertEquals(3000.00, $response->json('data.sale_info.total_paid'));
        $this->assertEquals(2000.00, $response->json('data.sale_info.remaining_balance'));
        $this->assertEquals('partial', $response->json('data.sale_info.payment_status'));
    }

    /** @test */
    public function validation_fails_for_invalid_data()
    {
        $invalidData = [
            'sale_id' => 99999, // Non-existent sale
            'amount' => -100, // Negative amount
            'payment_date' => 'invalid-date', // Invalid date
        ];

        $response = $this->post('/api/loan-payments', $invalidData, $this->headers);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors'
            ]);

        $this->assertFalse($response->json('success'));
        $this->assertArrayHasKey('sale_id', $response->json('errors'));
        $this->assertArrayHasKey('amount', $response->json('errors'));
        $this->assertArrayHasKey('payment_date', $response->json('errors'));
    }

    /** @test */
    public function cannot_access_other_tenant_payments()
    {
        // Create another tenant and user
        $otherTenant = \App\Models\Tenant::factory()->create();
        $otherUser = User::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $otherCustomer = \App\Models\Customer::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $otherSale = Sale::factory()->create([
            'tenant_id' => $otherTenant->id,
            'customer_id' => $otherCustomer->id,
        ]);

        $otherPayment = LoanPayment::factory()->create([
            'sale_id' => $otherSale->id,
            'user_id' => $otherUser->id,
        ]);

        // Try to access the other tenant's payment
        $response = $this->get("/api/loan-payments/{$otherPayment->id}", $this->headers);

        $response->assertStatus(403);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('Unauthorized access to this sale', $response->json('message'));
    }

    /** @test */
    public function can_filter_payments_by_date_range()
    {
        // Create payments with different dates
        LoanPayment::factory()->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'payment_date' => '2024-01-15',
        ]);

        LoanPayment::factory()->create([
            'sale_id' => $this->sale->id,
            'user_id' => $this->user->id,
            'amount' => 2000.00,
            'payment_date' => '2024-02-15',
        ]);

        $response = $this->get('/api/loan-payments?from_date=2024-02-01&to_date=2024-02-28', $this->headers);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals(1, count($response->json('data.data')));
        $this->assertEquals(2000.00, $response->json('data.data.0.amount'));
    }
}
