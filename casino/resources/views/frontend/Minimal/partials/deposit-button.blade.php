{{--
    Stripe Deposit Button Partial (Phase 2)
    Usage: @include('frontend.Minimal.partials.deposit-button', ['amount' => 10])
    Or with custom amounts:
    @include('frontend.Minimal.partials.deposit-button', ['amounts' => [10, 25, 50, 100]])
--}}

@auth
    @php
        $depositAmounts = $amounts ?? [10, 25, 50, 100];
        $userId = auth()->id();
    @endphp

    <div class="deposit-widget" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        @if(isset($amount))
            {{-- Single amount button --}}
            <a href="{{ route('deposit.checkout', ['userId' => $userId, 'amount' => $amount]) }}"
               class="btn btn-primary deposit-btn"
               style="background:#6366f1;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;font-size:14px;display:inline-flex;align-items:center;gap:6px;">
                💳 Déposer {{ number_format($amount, 0) }} €
            </a>
        @else
            {{-- Multiple amount buttons --}}
            @foreach($depositAmounts as $amt)
                <a href="{{ route('deposit.checkout', ['userId' => $userId, 'amount' => $amt]) }}"
                   class="btn deposit-btn"
                   style="background:#6366f1;color:#fff;padding:8px 16px;border-radius:6px;text-decoration:none;font-weight:600;font-size:13px;">
                    +{{ number_format($amt, 0) }} €
                </a>
            @endforeach
        @endif
    </div>
@endauth
