<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
          xmlns:o="urn:schemas-microsoft-com:office:office"
          xmlns:x="urn:schemas-microsoft-com:office:excel"
          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

 <Styles>
  <Style ss:ID="titre">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Bold="1" ss:Size="14" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#EA580C" ss:Pattern="Solid"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2"/></Borders>
  </Style>
  <Style ss:ID="soustitre">
   <Font ss:Bold="1" ss:Size="10" ss:Color="#7C2D12"/>
   <Interior ss:Color="#FFEDD5" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="infos">
   <Font ss:Size="9" ss:Color="#374151"/>
   <Interior ss:Color="#FFF7ED" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="th">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Bold="1" ss:Size="9" ss:Color="#FFFFFF"/>
   <Interior ss:Color="#374151" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#4B5563"/>
   </Borders>
  </Style>
  <Style ss:ID="td">
   <Alignment ss:Vertical="Center"/>
   <Font ss:Size="9"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
   </Borders>
  </Style>
  <Style ss:ID="td_alt">
   <Alignment ss:Vertical="Center"/>
   <Font ss:Size="9"/>
   <Interior ss:Color="#F9FAFB" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
   </Borders>
  </Style>
  <Style ss:ID="num">
   <Alignment ss:Horizontal="Right" ss:Vertical="Center"/>
   <Font ss:Size="9"/>
   <Borders>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/>
   </Borders>
  </Style>
  <Style ss:ID="paye">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Bold="1" ss:Size="9" ss:Color="#15803D"/>
   <Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="retard">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Bold="1" ss:Size="9" ss:Color="#DC2626"/>
   <Interior ss:Color="#FEE2E2" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="attente">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Bold="1" ss:Size="9" ss:Color="#D97706"/>
   <Interior ss:Color="#FEF3C7" ss:Pattern="Solid"/>
  </Style>
  <Style ss:ID="recap_label">
   <Font ss:Bold="1" ss:Size="10" ss:Color="#374151"/>
   <Interior ss:Color="#F3F4F6" ss:Pattern="Solid"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E5E7EB"/></Borders>
  </Style>
  <Style ss:ID="recap_moins">
   <Font ss:Size="10" ss:Color="#DC2626"/>
   <Interior ss:Color="#FFF1F2" ss:Pattern="Solid"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FECDD3"/></Borders>
  </Style>
  <Style ss:ID="recap_total">
   <Font ss:Bold="1" ss:Size="11" ss:Color="#15803D"/>
   <Interior ss:Color="#DCFCE7" ss:Pattern="Solid"/>
   <Borders>
    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#15803D"/>
    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="2" ss:Color="#15803D"/>
   </Borders>
  </Style>
  <Style ss:ID="sig_head">
   <Font ss:Bold="1" ss:Size="10" ss:Color="#374151"/>
   <Interior ss:Color="#F9FAFB" ss:Pattern="Solid"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/></Borders>
  </Style>
  <Style ss:ID="sig_line">
   <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
   <Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#9CA3AF"/></Borders>
  </Style>
  <Style ss:ID="footer_style">
   <Alignment ss:Horizontal="Center"/>
   <Font ss:Italic="1" ss:Size="8" ss:Color="#9CA3AF"/>
   <Interior ss:Color="#F9FAFB" ss:Pattern="Solid"/>
  </Style>
 </Styles>

 <Worksheet ss:Name="Relevé paiements">
  <Table ss:DefaultColumnWidth="90" ss:DefaultRowHeight="16">

   {{-- Col widths --}}
   <Column ss:Width="160"/>{{-- Bien --}}
   <Column ss:Width="130"/>{{-- Locataire --}}
   <Column ss:Width="80"/>{{-- Échéance --}}
   <Column ss:Width="90"/>{{-- Loyer --}}
   <Column ss:Width="80"/>{{-- Charges --}}
   <Column ss:Width="90"/>{{-- Frais ag --}}
   <Column ss:Width="100"/>{{-- Total --}}
   <Column ss:Width="80"/>{{-- Statut --}}
   <Column ss:Width="90"/>{{-- Date paiement --}}

   {{-- Titre --}}
   <Row ss:Height="30">
    <Cell ss:MergeAcross="8" ss:StyleID="titre">
     <Data ss:Type="String">ImmoGest — Relevé de paiements — {{ $periodeLabel }}</Data>
    </Cell>
   </Row>

   {{-- Infos période --}}
   <Row ss:Height="18">
    <Cell ss:MergeAcross="4" ss:StyleID="soustitre">
     <Data ss:Type="String">Propriétaire : {{ $user->name }}</Data>
    </Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="infos">
     <Data ss:Type="String">Période : {{ $dateDebut->format('d/m/Y') }} → {{ $dateFin->format('d/m/Y') }}</Data>
    </Cell>
   </Row>
   <Row ss:Height="16">
    <Cell ss:MergeAcross="4" ss:StyleID="infos">
     <Data ss:Type="String">Généré le {{ now()->format('d/m/Y') }} à {{ now()->format('H:i') }}</Data>
    </Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="infos">
     <Data ss:Type="String">{{ $paiements->count() }} paiement(s) — Payés : {{ $paiements->where('statut','paye')->count() }}</Data>
    </Cell>
   </Row>

   {{-- Ligne vide --}}
   <Row ss:Height="8"><Cell ss:MergeAcross="8"><Data ss:Type="String"></Data></Cell></Row>

   {{-- En-têtes colonnes --}}
   <Row ss:Height="22">
    <Cell ss:StyleID="th"><Data ss:Type="String">Bien</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Locataire</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Échéance</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Loyer ({{ $devSymbole }})</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Charges ({{ $devSymbole }})</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Frais agence</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Total ({{ $devSymbole }})</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Statut</Data></Cell>
    <Cell ss:StyleID="th"><Data ss:Type="String">Date paiement</Data></Cell>
   </Row>

   {{-- Données --}}
   @forelse($paiements as $i => $p)
   @php
    $enRetard = $p->statut === 'en_attente' && $p->date_echeance->isPast();
    $statLabel = $p->statut === 'paye' ? 'Payé' : ($enRetard ? 'En retard' : 'En attente');
    $statStyle = $p->statut === 'paye' ? 'paye' : ($enRetard ? 'retard' : 'attente');
    $rowStyle  = $i % 2 === 0 ? 'td' : 'td_alt';
    $fraisAg   = round((float)$p->location->loyer_mensuel * (float)$p->location->frais_agence / 100, 0);
    $fraisLabel = $p->location->frais_agence > 0
        ? $p->location->frais_agence . '% (' . number_format($fraisAg, 0, ',', ' ') . ')'
        : '—';
   @endphp
   <Row ss:Height="18">
    <Cell ss:StyleID="{{ $rowStyle }}"><Data ss:Type="String">{{ optional($p->location->bien)->titre ?? '—' }}</Data></Cell>
    <Cell ss:StyleID="{{ $rowStyle }}"><Data ss:Type="String">{{ $p->location->locataire->name ?? '—' }}</Data></Cell>
    <Cell ss:StyleID="{{ $rowStyle }}"><Data ss:Type="String">{{ $p->date_echeance->format('d/m/Y') }}</Data></Cell>
    <Cell ss:StyleID="num"><Data ss:Type="Number">{{ (float)$p->location->loyer_mensuel }}</Data></Cell>
    <Cell ss:StyleID="num"><Data ss:Type="Number">{{ (float)$p->location->charges }}</Data></Cell>
    <Cell ss:StyleID="{{ $rowStyle }}"><Data ss:Type="String">{{ $fraisLabel }}</Data></Cell>
    <Cell ss:StyleID="num"><Data ss:Type="Number">{{ (float)$p->montant }}</Data></Cell>
    <Cell ss:StyleID="{{ $statStyle }}"><Data ss:Type="String">{{ $statLabel }}</Data></Cell>
    <Cell ss:StyleID="{{ $rowStyle }}"><Data ss:Type="String">{{ $p->date_paiement ? $p->date_paiement->format('d/m/Y') : '—' }}</Data></Cell>
   </Row>
   @empty
   <Row><Cell ss:MergeAcross="8" ss:StyleID="td"><Data ss:Type="String">Aucun paiement sur cette période.</Data></Cell></Row>
   @endforelse

   {{-- Ligne vide --}}
   <Row ss:Height="12"><Cell ss:MergeAcross="8"><Data ss:Type="String"></Data></Cell></Row>

   {{-- ── Récapitulatif ── --}}
   <Row ss:Height="20">
    <Cell ss:MergeAcross="5" ss:StyleID="recap_label">
     <Data ss:Type="String">RÉCAPITULATIF FINANCIER — {{ $periodeLabel }}</Data>
    </Cell>
   </Row>
   <Row ss:Height="18">
    <Cell ss:MergeAcross="4" ss:StyleID="td">
     <Data ss:Type="String">Total recouvrement (loyers perçus)</Data>
    </Cell>
    <Cell ss:StyleID="num"><Data ss:Type="Number">{{ (float)$totalRecouvrement }}</Data></Cell>
   </Row>
   <Row ss:Height="18">
    <Cell ss:MergeAcross="4" ss:StyleID="recap_moins">
     <Data ss:Type="String">(–) Montant interventions sur la période</Data>
    </Cell>
    <Cell ss:StyleID="num"><Data ss:Type="Number">{{ (float)$totalInterventions }}</Data></Cell>
   </Row>
   <Row ss:Height="18">
    <Cell ss:MergeAcross="4" ss:StyleID="recap_moins">
     <Data ss:Type="String">(–) Frais d'agence (commissions sur loyers perçus)</Data>
    </Cell>
    <Cell ss:StyleID="num"><Data ss:Type="Number">{{ (float)$totalFraisAgence }}</Data></Cell>
   </Row>
   <Row ss:Height="22">
    <Cell ss:MergeAcross="4" ss:StyleID="recap_total">
     <Data ss:Type="String">= TOTAL NET PROPRIÉTAIRE</Data>
    </Cell>
    <Cell ss:StyleID="recap_total"><Data ss:Type="Number">{{ (float)$totalNet }}</Data></Cell>
   </Row>

   {{-- Ligne vide --}}
   <Row ss:Height="20"><Cell ss:MergeAcross="8"><Data ss:Type="String"></Data></Cell></Row>

   {{-- ── Signatures ── --}}
   <Row ss:Height="20">
    <Cell ss:MergeAcross="8" ss:StyleID="recap_label">
     <Data ss:Type="String">SIGNATURES</Data>
    </Cell>
   </Row>
   <Row ss:Height="18">
    <Cell ss:MergeAcross="3" ss:StyleID="sig_head">
     <Data ss:Type="String">L'Agence immobilière</Data>
    </Cell>
    <Cell><Data ss:Type="String"></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sig_head">
     <Data ss:Type="String">Le Propriétaire — {{ $user->name }}</Data>
    </Cell>
   </Row>
   <Row ss:Height="16">
    <Cell ss:MergeAcross="3" ss:StyleID="td">
     <Data ss:Type="String">Nom : _________________________________</Data>
    </Cell>
    <Cell><Data ss:Type="String"></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="td">
     <Data ss:Type="String">Lu et approuvé</Data>
    </Cell>
   </Row>
   <Row ss:Height="36">
    <Cell ss:MergeAcross="3" ss:StyleID="sig_line">
     <Data ss:Type="String"></Data>
    </Cell>
    <Cell><Data ss:Type="String"></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="sig_line">
     <Data ss:Type="String"></Data>
    </Cell>
   </Row>
   <Row ss:Height="16">
    <Cell ss:MergeAcross="3" ss:StyleID="td">
     <Data ss:Type="String">Date : _____ / _____ / _______</Data>
    </Cell>
    <Cell><Data ss:Type="String"></Data></Cell>
    <Cell ss:MergeAcross="3" ss:StyleID="td">
     <Data ss:Type="String">Date : _____ / _____ / _______</Data>
    </Cell>
   </Row>

   {{-- Ligne vide --}}
   <Row ss:Height="12"><Cell ss:MergeAcross="8"><Data ss:Type="String"></Data></Cell></Row>

   {{-- Footer --}}
   <Row ss:Height="14">
    <Cell ss:MergeAcross="8" ss:StyleID="footer_style">
     <Data ss:Type="String">Document généré par ImmoGest · {{ now()->format('d/m/Y H:i') }} · Confidentiel</Data>
    </Cell>
   </Row>

  </Table>
 </Worksheet>
</Workbook>
