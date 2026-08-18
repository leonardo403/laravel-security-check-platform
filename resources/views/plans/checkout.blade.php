@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="mb-6">
        <a href="{{ route('plans.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('plans.plans') }}
        </a>
    </div>

    <div class="bg-slate-900/60 border border-slate-700/50 rounded-2xl p-8 backdrop-blur-sm">
        <h1 class="text-xl font-bold text-white mb-1">{{ __('plans.subscribe_plan', ['plan' => trans()->has('plans.name_'.$plan->slug) ? __('plans.name_'.$plan->slug) : $plan->name]) }}</h1>
        <div class="mt-3 mb-6">
            <span class="text-3xl font-bold text-white">${{ number_format($plan->price, 2) }}</span>
            <span class="text-sm text-slate-500">{{ __('plans.per_month') }}</span>
        </div>

        <div class="mb-6 space-y-2.5">
            <li class="flex items-center gap-2.5 text-sm text-slate-300">
                <div class="w-5 h-5 rounded-full bg-teal-500/10 flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                {{ __('plans.scans_per_month', ['count' => $plan->max_scans_per_month]) }}
            </li>
            @foreach($plan->features as $feature => $enabled)
                @if($enabled)
                <li class="flex items-center gap-2.5 text-sm text-slate-300">
                    <div class="w-5 h-5 rounded-full bg-teal-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-3 h-3 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    {{ trans()->has('plans.features_'.$feature) ? __('plans.features_'.$feature) : ucfirst(str_replace('_', ' ', $feature)) }}
                </li>
                @endif
            @endforeach
        </div>

        <div class="border-t border-slate-700/50 pt-6 mt-6">
            <div id="payment-element" class="mb-4"></div>
            <div id="payment-message" class="mb-4 text-sm text-rose-400 hidden"></div>

            <button id="submit"
                class="w-full rounded-xl bg-gradient-to-r from-teal-500 to-teal-600 py-3 font-semibold text-white hover:from-teal-400 hover:to-teal-500 transition-all duration-300 shadow-lg shadow-teal-500/20 hover:shadow-teal-500/30 disabled:opacity-50 disabled:cursor-not-allowed">
                {{ __('plans.pay', ['amount' => number_format($plan->price, 2)]) }}
            </button>

            <p class="text-xs text-slate-500 mt-4 text-center flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                {{ __('plans.secure_payment') }}
            </p>
        </div>
    </div>
</div>

<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ $stripeKey }}');
    const clientSecret = '{{ $clientSecret }}';
    const returnUrl = '{{ route('subscription.confirm') }}';
    const elements = stripe.elements({ clientSecret });

    const paymentElement = elements.create('payment', {
        layout: 'tabs',
    });
    paymentElement.mount('#payment-element');

    const submitButton = document.getElementById('submit');
    const message = document.getElementById('payment-message');

    submitButton.addEventListener('click', async () => {
        message.classList.add('hidden');
        submitButton.disabled = true;

        try {
            const { error: submitError } = await elements.submit();

            if (submitError) {
                message.textContent = submitError.message;
                message.classList.remove('hidden');
                submitButton.disabled = false;
                return;
            }

            const { error } = await stripe.confirmPayment({
                elements,
                clientSecret,
                confirmParams: {
                    return_url: returnUrl,
                },
            });

            if (error) {
                message.textContent = error.message;
                message.classList.remove('hidden');
                submitButton.disabled = false;
                return;
            }

            const paymentIntentId = clientSecret.split('_secret_')[0];
            window.location.href = returnUrl + '?payment_intent=' + paymentIntentId;
        } catch (err) {
            message.textContent = err.message || '{{ __('plans.payment_error') }}';
            message.classList.remove('hidden');
            submitButton.disabled = false;
        }
    });
</script>
@endsection
