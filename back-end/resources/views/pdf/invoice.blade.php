<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .logo-box { font-size: 20px; font-weight: bold; color: #2563eb; }
        .facture-title { text-align: right; }
        .facture-title h1 { font-size: 22px; margin: 0; }
        .facture-title p { margin: 2px 0; color: #64748b; }
        .infos { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .infos-block h3 { font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .infos-block p { margin: 0; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f1f5f9; text-align: left; padding: 8px; font-size: 10px; text-transform: uppercase; color: #64748b; }
        td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
        .text-right { text-align: right; }
        .totaux { width: 250px; margin-left: auto; }
        .totaux div { display: flex; justify-content: space-between; padding: 4px 0; }
        .totaux .total-final { font-weight: bold; font-size: 14px; border-top: 2px solid #1e293b; margin-top: 6px; padding-top: 8px; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 10px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .badge-payee { background: #dcfce7; color: #15803d; }
        .badge-envoyee { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-box">
            @if($company->logo_path)
                <img src="{{ public_path('storage/' . $company->logo_path) }}" style="max-height: 40px; max-width: 160px;">
            @else
                {{ $company->nom_entreprise }}
            @endif
        </div>
        <div class="facture-title">
            <h1>FACTURE</h1>
            <p>{{ $invoice->numero }}</p>
            <span class="badge {{ $invoice->statut === 'payee' ? 'badge-payee' : 'badge-envoyee' }}">
                {{ strtoupper($invoice->statut) }}
            </span>
        </div>
    </div>

    <div class="infos">
        <div class="infos-block">
            <h3>Facturé à</h3>
            <p><strong>{{ $invoice->client->nom }}</strong></p>
            <p>{{ $invoice->client->adresse }}</p>
            <p>{{ $invoice->client->ville }}</p>
            <p>{{ $invoice->client->email }}</p>
            @if($invoice->client->nif_stat)
                <p>NIF/STAT : {{ $invoice->client->nif_stat }}</p>
            @endif
        </div>
        <div class="infos-block">
            <h3>Détails</h3>
            <p>Date d'émission : {{ $invoice->date_emission->format('d/m/Y') }}</p>
            <p>Date d'échéance : {{ $invoice->date_echeance->format('d/m/Y') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th class="text-right">Qté</th>
                <th class="text-right">PU HT</th>
                <th class="text-right">TVA</th>
                <th class="text-right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->nom }}</td>
                    <td class="text-right">{{ $item->quantite }} {{ $item->product->unite }}</td>
                    <td class="text-right">{{ number_format($item->prix_unitaire_ht, 2, ',', ' ') }} {{ $company->devise }}</td>
                    <td class="text-right">{{ $item->tva_taux }}%</td>
                    <td class="text-right">{{ number_format($item->total_ligne, 2, ',', ' ') }} Ar</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totaux">
        <div><span>Sous-total HT</span><span>{{ number_format($invoice->sous_total_ht, 2, ',', ' ') }} Ar</span></div>
        <div><span>TVA</span><span>{{ number_format($invoice->total_tva, 2, ',', ' ') }} Ar</span></div>
        <div class="total-final"><span>Total TTC</span><span>{{ number_format($invoice->total_ttc, 2, ',', ' ') }} Ar</span></div>
    </div>

    <div class="footer">
        <p>
            <strong>{{ $company->nom_entreprise }}</strong>
            @if($company->adresse) — {{ $company->adresse }} @endif
            @if($company->ville) , {{ $company->ville }} @endif
        </p>
        <p>
            @if($company->email) {{ $company->email }} @endif
            @if($company->telephone) — {{ $company->telephone }} @endif
            @if($company->nif_stat) — NIF/STAT : {{ $company->nif_stat }} @endif
        </p>
        @if($company->conditions_paiement)
            <p style="margin-top: 8px; font-style: italic;">{{ $company->conditions_paiement }}</p>
        @endif
    </div>
</body>
</html>