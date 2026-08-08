@extends('layouts.app')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-1">Assinar {{ $plan->name }}</h1>
        <p class="text-4xl font-bold my-4">R$ {{ number_format($plan->price, 2) }}<span class="text-sm text-gray-500">/mês</span></p>

        <div class="mb-6 space-y-2">
            <li class="flex items-center">
                <span class="mr-2">✓</span>
                {{ $plan->max_scans_per_month }} scans/mês
            </li>
            @foreach($plan->features as $feature => $enabled)
                @if($enabled)
                <li class="flex items-center">
                    <span class="mr-2">✓</span>
                    {{ ucfirst(str_replace('_', ' ', $feature)) }}
                </li>
                @endif
            @endforeach
        </div>

        <div id="payment-element" class="mb-4"></div>
        <div id="payment-message" class="mb-4 text-sm text-red-600 hidden"></div>

        <button id="submit"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
            Pagar R$ {{ number_format($plan->price, 2) }}
        </button>

        <p class="text-xs text-gray-500 mt-4 text-center">
            Pagamento seguro processado pelo Stripe. Você será redirecionado após a confirmação.
        </p>
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
            message.textContent = err.message || 'Ocorreu um erro ao processar o pagamento. Tente novamente.';
            message.classList.remove('hidden');
            submitButton.disabled = false;
        }
    });
</script>
@endsection
