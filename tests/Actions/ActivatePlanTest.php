<?php

namespace Osiset\ShopifyApp\Test\Actions;

use Osiset\ShopifyApp\Test\TestCase;
use Osiset\ShopifyApp\Storage\Models\Plan;
use Osiset\ShopifyApp\Actions\ActivatePlan;
use Osiset\ShopifyApp\Objects\Values\ChargeId;
use Osiset\ShopifyApp\Objects\Values\ChargeReference;
use Osiset\ShopifyApp\Storage\Models\Charge;
use Osiset\ShopifyApp\Test\Stubs\Api as ApiStub;

class ActivatePlanTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->action = $this->app->make(ActivatePlan::class);
    }

    public function testRun(): void
    {
        // Create a plan
        $plan = factory(Plan::class)->states('type_recurring')->create();

        // Create the shop with the plan attached
        $shop = factory($this->model)->create([
            'plan_id' => $plan->getId()->toNative()
        ]);

        // Create a charge for the plan and shop
        factory(Charge::class)->states('type_recurring')->create([
            'charge_id' => 12345,
            'plan_id'   => $plan->getId()->toNative(),
            'user_id'   => $shop->getId()->toNative()
        ]);

        // Setup API stub
        $this->setApiStub();
        ApiStub::stubResponses(['post_recurring_application_charges']);

        // Activate the charge
        $result = call_user_func(
            $this->action,
            $shop->getId(),
            $plan->getId(),
            new ChargeReference(12345)
        );

        $this->assertInstanceOf(ChargeId::class, $result);
    }
}
