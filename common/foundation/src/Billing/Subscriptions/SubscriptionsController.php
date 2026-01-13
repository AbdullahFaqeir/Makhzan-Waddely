<?php

namespace Common\Billing\Subscriptions;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Common\Core\BaseController;
use Illuminate\Validation\Rule;
use Common\Billing\Subscription;
use Common\Billing\Models\Price;
use Illuminate\Http\JsonResponse;
use Common\Billing\Models\Product;
use Illuminate\Support\Facades\Auth;
use Common\Database\Datasource\Datasource;
use Illuminate\Contracts\Routing\ResponseFactory;

class SubscriptionsController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected Subscription $subscription,
    ) {
        $this->middleware('auth');
    }

    public function index(): Response|JsonResponse|ResponseFactory
    {
        $this->authorize('index', Subscription::class);

        $dataSource = new Datasource($this->subscription->with(['user']),
            $this->request->all(),);

        $pagination = $dataSource->paginate()
                                 ->toArray();

        if (config('app.demo')) {
            $pagination['data'] = $this->redactEmails($pagination['data'],
                'user.email',);
        }

        return $this->success(['pagination' => $pagination]);
    }

    public function store(): Response|JsonResponse|ResponseFactory
    {
        $this->authorize('update', Subscription::class);
        $this->blockOnDemoSite();

        $data = $this->validate($this->request, [
            'user_id'     => 'required|exists:users,id|unique:subscriptions',
            'renews_at'   => 'required_without:ends_at|date|nullable',
            'ends_at'     => 'required_without:renews_at|date|nullable',
            'product_id'  => 'required|integer|exists:products,id',
            'price_id'    => 'required|integer|exists:prices,id',
            'description' => 'string|nullable',
        ]);

        $data['renews_at'] = Carbon::parse($data['renews_at']);
        $data['ends_at'] = Carbon::parse($data['ends_at']);

        $subscription = $this->subscription->create($data);

        return $this->success(['subscription' => $subscription]);
    }

    public function update(int $id): Response|JsonResponse|ResponseFactory
    {
        $subscription = Subscription::query()
                                    ->findOrFail($id);

        $this->authorize('show', $subscription);
        $this->blockOnDemoSite();

        $data = $this->validate($this->request, [
            'user_id'     => [
                'required',
                'exists:users,id',
                Rule::unique('subscriptions')
                    ->ignore($subscription->id),
            ],
            'renews_at'   => 'date|nullable',
            'ends_at'     => 'date|nullable',
            'product_id'  => 'required|integer|exists:products,id',
            'price_id'    => 'required|integer|exists:prices,id',
            'description' => 'string|nullable',
        ]);

        $subscription->fill($data)
                     ->save();

        return $this->success(['subscription' => $subscription]);
    }

    public function changePlan(int $id): Response|JsonResponse|ResponseFactory
    {
        $subscription = Subscription::query()
                                    ->findOrFail($id);
        $this->authorize('show', $subscription);

        if ($subscription->user_id !== Auth::id()) {
            $this->blockOnDemoSite();
        }

        $data = $this->validate($this->request, [
            'newProductId' => 'required|integer|exists:products,id',
            'newPriceId'   => 'required|integer|exists:prices,id',
        ]);

        $newProduct = Product::findOrFail($data['newProductId']);
        $newPrice = Price::findOrFail($data['newPriceId']);

        $subscription->changePlan($newProduct, $newPrice);

        $user = $subscription->user()
                             ->first();
        return $this->success(['user' => $user->load('subscriptions.product')]);
    }

    public function cancel(int $id): Response|JsonResponse|ResponseFactory
    {
        $subscription = Subscription::query()
                                    ->findOrFail($id);
        $this->authorize('show', $subscription);

        if ($subscription->user_id !== Auth::id()) {
            $this->blockOnDemoSite();
        }

        $this->validate($this->request, [
            'delete' => 'boolean',
        ]);

        if ($this->request->get('delete')) {
            $subscription->cancelAndDelete();
        } else {
            $subscription->cancel();
        }

        return $this->success();
    }

    public function resume(int $id): Response|JsonResponse|ResponseFactory
    {
        $subscription = Subscription::query()
                                    ->findOrFail($id);
        $this->authorize('show', $subscription);

        if ($subscription->user_id !== Auth::id()) {
            $this->blockOnDemoSite();
        }

        $subscription->resume();

        return $this->success(['subscription' => $subscription]);
    }
}
