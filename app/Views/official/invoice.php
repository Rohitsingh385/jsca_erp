<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice <?= esc($invoice['invoice_number']) ?> — JSCA</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 13px; color: #333; background: #f5f5f5; }
    .page { max-width: 720px; margin: 30px auto; background: #fff; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,0.1); }

    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #1a3a5c; padding-bottom: 20px; margin-bottom: 24px; }
    .header .logo { font-size: 22px; font-weight: 800; color: #1a3a5c; }
    .header .logo span { color: #2ecc71; }
    .header .inv-meta { text-align: right; }
    .header .inv-meta .inv-number { font-size: 18px; font-weight: 700; color: #1a3a5c; }
    .header .inv-meta .inv-date { color: #888; font-size: 12px; margin-top: 4px; }

    .status-badge { display: inline-block; padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin-top: 6px; }
    .status-generated { background: #fff3cd; color: #856404; }
    .status-paid { background: #d1e7dd; color: #0a3622; }

    .section { margin-bottom: 24px; }
    .section-title { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #888; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 4px; }

    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }

    .detail-row { display: flex; margin-bottom: 6px; }
    .detail-label { width: 130px; flex-shrink: 0; color: #888; font-size: 12px; }
    .detail-value { font-weight: 600; color: #222; }

    .amount-box { background: #1a3a5c; color: #fff; border-radius: 8px; padding: 20px 24px; text-align: center; margin: 24px 0; }
    .amount-box .amount-label { font-size: 11px; text-transform: uppercase; letter-spacing: .1em; opacity: .7; }
    .amount-box .amount-value { font-size: 36px; font-weight: 800; margin-top: 4px; }

    .bank-section { background: #f8f9fa; border-radius: 8px; padding: 16px 20px; }

    .footer { margin-top: 32px; padding-top: 16px; border-top: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
    .footer .note { font-size: 11px; color: #aaa; }

    .no-bank { color: #e74c3c; font-size: 12px; font-style: italic; }

    @media print {
      body { background: #fff; }
      .page { box-shadow: none; margin: 0; padding: 30px; }
      .print-btn { display: none; }
    }
  </style>
</head>
<body>

<div class="page">

  <!-- Header -->
  <div class="header">
    <div>
      <div class="logo">🏏 JSCA <span>ERP</span></div>
      <div style="font-size:11px;color:#888;margin-top:4px;">Jharkhand State Cricket Association</div>
      <div class="status-badge <?= $invoice['status'] === 'Paid' ? 'status-paid' : 'status-generated' ?>">
        <?= $invoice['status'] === 'Paid' ? '✓ Paid' : '⏳ Payment Pending' ?>
      </div>
    </div>
    <div class="inv-meta">
      <div class="inv-number"><?= esc($invoice['invoice_number']) ?></div>
      <div class="inv-date">Generated: <?= date('d M Y', strtotime($invoice['generated_at'])) ?></div>
      <?php if ($invoice['paid_at']): ?>
        <div class="inv-date" style="color:#2ecc71;">Paid: <?= date('d M Y', strtotime($invoice['paid_at'])) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <div class="two-col">

    <!-- Official Details -->
    <div class="section">
      <div class="section-title">Official</div>
      <div class="detail-row"><span class="detail-label">Name</span><span class="detail-value"><?= esc($invoice['snap_name']) ?></span></div>
      <div class="detail-row"><span class="detail-label">JSCA ID</span><span class="detail-value"><?= esc($invoice['snap_jsca_id']) ?></span></div>
      <div class="detail-row"><span class="detail-label">Type</span><span class="detail-value"><?= esc($invoice['snap_type']) ?></span></div>
      <?php if ($invoice['snap_grade']): ?>
      <div class="detail-row"><span class="detail-label">Grade</span><span class="detail-value"><?= esc($invoice['snap_grade']) ?></span></div>
      <?php endif; ?>
      <?php if ($invoice['snap_phone']): ?>
      <div class="detail-row"><span class="detail-label">Phone</span><span class="detail-value"><?= esc($invoice['snap_phone']) ?></span></div>
      <?php endif; ?>
    </div>

    <!-- Match Details -->
    <div class="section">
      <div class="section-title">Match Details</div>
      <div class="detail-row"><span class="detail-label">Tournament</span><span class="detail-value"><?= esc($invoice['snap_tournament']) ?></span></div>
      <div class="detail-row"><span class="detail-label">Match</span><span class="detail-value"><?= esc($invoice['snap_match_number']) ?></span></div>
      <div class="detail-row"><span class="detail-label">Teams</span><span class="detail-value"><?= esc($invoice['snap_teams']) ?></span></div>
      <div class="detail-row"><span class="detail-label">Date</span><span class="detail-value"><?= date('d M Y', strtotime($invoice['snap_match_date'])) ?></span></div>
      <?php if ($invoice['snap_venue']): ?>
      <div class="detail-row"><span class="detail-label">Venue</span><span class="detail-value"><?= esc($invoice['snap_venue']) ?></span></div>
      <?php endif; ?>
      <div class="detail-row"><span class="detail-label">Role</span><span class="detail-value"><?= esc($invoice['snap_role']) ?></span></div>
    </div>

  </div>

  <!-- Amount -->
  <div class="amount-box">
    <div class="amount-label">Match Fee</div>
    <div class="amount-value">₹<?= number_format($invoice['amount'], 2) ?></div>
  </div>

  <!-- Bank Details -->
  <div class="section">
    <div class="section-title">Payment Details</div>
    <?php if ($invoice['snap_bank_account']): ?>
    <div class="bank-section">
      <div class="two-col">
        <div>
          <div class="detail-row"><span class="detail-label">Bank</span><span class="detail-value"><?= esc($invoice['snap_bank_name'] ?? '—') ?></span></div>
          <div class="detail-row"><span class="detail-label">Account No.</span><span class="detail-value"><?= esc($invoice['snap_bank_account']) ?></span></div>
        </div>
        <div>
          <div class="detail-row"><span class="detail-label">IFSC Code</span><span class="detail-value"><?= esc($invoice['snap_bank_ifsc'] ?? '—') ?></span></div>
        </div>
      </div>
    </div>
    <?php else: ?>
      <p class="no-bank">⚠ Bank details not on record. Please contact JSCA office to update your bank information.</p>
    <?php endif; ?>
  </div>

  <!-- Footer -->
  <div class="footer">
    <div class="note">This is a system-generated invoice. No signature required.</div>
    <button class="print-btn" onclick="window.print()"
      style="background:#1a3a5c;color:#fff;border:none;padding:8px 20px;border-radius:6px;cursor:pointer;font-size:13px;">
      🖨 Print / Download PDF
    </button>
  </div>

</div>

</body>
</html>
