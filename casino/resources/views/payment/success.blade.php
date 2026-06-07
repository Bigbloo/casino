@extends('frontend.Minimal.layouts.app')

@section('page-title', 'Dépôt réussi')

@section('content')
<div class="container" style="max-width:600px;margin:60px auto;text-align:center;">
    <div style="background:#1a2035;border-radius:12px;padding:40px;border:1px solid #2a3a5c;">
        <div style="font-size:64px;margin-bottom:20px;">✅</div>
        <h1 style="color:#4ade80;font-size:28px;margin-bottom:12px;">Paiement réussi !</h1>

        @if($intent)
            <p style="color:#94a3b8;font-size:16px;margin-bottom:8px;">
                Votre dépôt de <strong style="color:#fff;">{{ number_format($intent->amount, 2) }} {{ strtoupper($intent->currency) }}</strong> a été crédité sur votre compte.
            </p>
        @else
            <p style="color:#94a3b8;font-size:16px;margin-bottom:8px;">
                Votre paiement a été traité avec succès. Votre solde sera mis à jour sous peu.
            </p>
        @endif

        <p style="color:#64748b;font-size:14px;margin-bottom:30px;">
            Merci pour votre dépôt sur {{ settings('app_name') }}.
        </p>

        <a href="{{ url('/') }}" style="display:inline-block;background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">
            Retour à l'accueil
        </a>
    </div>
</div>
@endsection
