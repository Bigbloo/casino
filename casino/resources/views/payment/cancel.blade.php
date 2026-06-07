@extends('frontend.Minimal.layouts.app')

@section('page-title', 'Paiement annulé')

@section('content')
<div class="container" style="max-width:600px;margin:60px auto;text-align:center;">
    <div style="background:#1a2035;border-radius:12px;padding:40px;border:1px solid #2a3a5c;">
        <div style="font-size:64px;margin-bottom:20px;">❌</div>
        <h1 style="color:#f87171;font-size:28px;margin-bottom:12px;">Paiement annulé</h1>

        <p style="color:#94a3b8;font-size:16px;margin-bottom:8px;">
            Votre paiement a été annulé. Aucun montant n'a été débité.
        </p>

        <p style="color:#64748b;font-size:14px;margin-bottom:30px;">
            Si vous avez rencontré un problème, veuillez réessayer ou contacter le support.
        </p>

        <a href="{{ url('/') }}" style="display:inline-block;background:#6366f1;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;margin-right:10px;">
            Retour à l'accueil
        </a>
        <a href="javascript:history.back()" style="display:inline-block;background:#374151;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;font-size:15px;">
            Réessayer
        </a>
    </div>
</div>
@endsection
