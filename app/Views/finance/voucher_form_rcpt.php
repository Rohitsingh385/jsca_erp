<style>
    /* Fix for Bootstrap Datepicker Icons in BS5 */
    .datepicker .prev,
    .datepicker .next {
        visibility: visible !important;
        background-color: #f8f9fa !important;
    }

    .datepicker table tr td.active.active {
        background-color: var(--jsca-primary) !important;
        border-radius: 4px;
    }

    .locked-step {
        pointer-events: none;
        background-color: #e9ecef !important;
        opacity: 0.7;
        cursor: not-allowed;
    }
</style>
<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>" id="csrf_token_value">
<div class="card">
    <div class="card-header">
        <i class="bi bi-plus-circle me-2 text-success"></i>Payment Voucher
    </div>

    <div class="card-body">

        <div class="row g-3">

            <div class="col-md-3">
                <label class="form-label">Tournament Name</label>
                <select name="tournament" id="tournament" class="form-select form-select-sm">
                    <option value="">Select Tournament</option>
                    <?php foreach ($tournaments as $tour): ?>
                        <option value="<?= $tour['id'] ?>"><?= $tour['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Match</label>
                <select name="match" id="match" class="form-select form-select-sm locked-step">
                    <option value="">-- Select Tournament First --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Payment To</label>
                <select name="pay_to" id="pay_to" class="form-select form-select-sm locked-step">
                    <option value="">Select</option>
                    <option value="Teams">Teams</option>
                    <option value="Officials">Officials</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Team Name</label>
                <select name="team_names" id="team_names" class="form-select form-select-sm locked-step">
                    <option value="">-- Select Match First --</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Official Type</label>
                <select name="official_type" id="official_type" class="form-select form-select-sm locked-step">
                    <option value="">Select</option>
                    <?php foreach ($official_types as $off_type): ?>
                        <option value="<?= $off_type['id'] ?>"> <?= $off_type['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3" id="official_name_section">
                <label class="form-label">Official Name</label>
                <select name="official_name" id="official_name" class="form-select form-select-sm locked-step">
                    <option value="">-- Select Type First --</option>
                </select>
            </div>
        </div>

        <div class="row g-3" id = 'voucher_details_1'>

            <div class="col-md-4">
                <label class="form-label">Voucher No.</label>
                <input type="text" name="voucher_no" class="form-control form-control-sm" value="<?= $voucher; ?>" readonly>
            </div>

            <div class="col-md-4">
                <label class="form-label">Voucher Date</label>
                <input type="text" id="voucher_date" name="voucher_date" class="form-control form-control-sm">
            </div>

            <div class="col-md-4">
                <label class="form-label">Payment Mode</label>
                <select name="payment_mode" id="payment_mode" class="form-select form-select-sm locked-step">
                    <option value="">Select</option>
                    <option value="Cash">Cash</option>
                    <option value="Bank">Bank</option>
                </select>
            </div>
        </div>

        <div id="bank_details_section" class="col-md-12" style="display:none;">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Bank Account No.</label>
                    <select name="bank_account" id="bank_account" class="form-select form-select-sm">
                        <option value="">Select</option>
                        <?php foreach ($bank_acc as $acc): ?>
                            <option value="<?= $acc['id'] ?>" data-ifsc="<?= $acc['ifsc_code'] ?>">
                                <?= $acc['bank_name'] . ' - ' . $acc['acc_no'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">IFSC Code</label>
                    <input type="text" name="bank_ifsc" class="form-control form-control-sm">
                </div>

                <div class="col-md-4" id="payment_thrgh_section" style="display:none;">
                    <label class="form-label">Payment Through</label>
                    <select name="payment_thrgh" id="payment_thrgh" class="form-select form-select-sm">
                        <option value="">Select</option>
                        <option value="NEFT">NEFT</option>
                        <option value="RTGS">RTGS</option>
                        <option value="Cheque">Cheque</option>
                    </select>
                </div>

                <div class="col-md-4" id="ref_no_section" style="display:none;">
                    <label class="form-label" id="ref_label">Reference No.</label>
                    <input type="text" name="reference_no" class="form-control form-control-sm">
                </div>
            </div>
        </div>


        <div class="row g-3"  id = 'voucher_details_2'>

            <div class="col-md-3">
                <label class="form-label">Amount</label>
                <input type="number" name="amount" class="form-control form-control-sm">
            </div>

            <div class="col-md-3">
                <label class="form-label">Dr./Cr.</label>
                <select name="dr_cr" id="transaction_type" class="form-select form-select-sm locked-step">
                    <option value="">Select</option>
                    <option value="Dr">Dr.</option>
                    <option value="Cr">Cr.</option>
                </select>
            </div>




            <div class="col-md-4">
                <label class="form-label">Ledger Heads</label>
                <select name="ledger_id" id="ledger_head" class="form-select form-select-sm locked-step">
                    <option value="">-- Select Type First --</option>
                    <?php foreach ($ledger_heads as $head): ?>
                        <option value="<?= $head['id'] ?>" data-type="<?= $head['group_id'] ?>" style="display:none;">
                            <?= esc($head['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>




            <div class="col-12">
                <label>Narration</label>
                <textarea name="narrations" id="narrations" class="form-control form-control-sm"></textarea>
            </div>

            <div class="col-12">
                <button type="button" id="add_row" class="btn btn-primary btn-sm mt-4">Add to List</button>
            </div>

        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-sm" id="voucher_items_table">
                <thead class="table-light">
                    <tr>
                        <th width="5%">SL</th>
                        <th width="40%">Ledger Head</th>
                        <th width="20%">Dr. Amount</th>
                        <th width="20%">Cr. Amount</th>
                        <th width="5%">Action</th>
                    </tr>
                </thead>
                <tbody id="voucher_body">
                </tbody>
                <tfoot>
                    <tr class="fw-bold bg-light">
                        <td colspan="2" class="text-end">Total:</td>
                        <td id="total_dr">0.00</td>
                        <td id="total_cr">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div id="balance_status" class="badge bg-danger p-2">Unbalanced: Diff ₹0.00</div>
            <button type="submit" id="final_save_btn" class="btn btn-success" disabled>
                <i class="bi bi-cloud-upload me-1"></i>Finalize Voucher
            </button>
        </div>

    </div>
</div>


<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#voucher_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: "bottom auto",
            startDate: '01-04-2025',
            endDate: new Date()
        }).datepicker('setDate', new Date());
    });

    $(document).ready(function() {

        // 1. Fetch Matches when Tournament changes
        $('#tournament').on('change', function() {
            const tournamentId = $(this).val();
            const $matchSelect = $('#match');
            const $teamSelect = $('#team_names');

            // Reset both dropdowns
            $matchSelect.html('<option value="">Loading...</option>');
            $teamSelect.html('<option value="">-- Select Match First --</option>');

            $matchSelect.val('').removeClass('locked-step').html('<option value="">-- Select Tournament First --</option>');
            $('#pay_to, #team_names, #official_type, #official_name').val('').addClass('locked-step');

            if (!tournamentId) {
                $matchSelect.html('<option value="">-- Select Tournament First --</option>');
                return;
            }

            $.ajax({
                url: '/finance/voucher/getMatchesByTournament', // Update this path to your route
                type: 'POST',
                data: {
                    tournament_id: tournamentId,
                    "<?= csrf_token() ?>": $('#csrf_token_value').val()
                },
                dataType: 'json',
                success: function(data) {
                    let options = '<option value="">Select Match</option>';
                    $.each(data, function(index, match) {
                        // We store team info in data-attributes of the match option
                        options += `<option value="${match.id}" 
                                    data-ta-id="${match.team_a_id}" data-ta-name="${match.team_a}"
                                    data-tb-id="${match.team_b_id}" data-tb-name="${match.team_b}">
                                    ${match.team_a} VS ${match.team_b}
                                </option>`;
                    });
                    $matchSelect.html(options);
                },
                error: function() {
                    alert('Error fetching matches. Please check your controller route.');
                }
            });
        });

        // 2. Populate Teams when Match changes (Local population from data-attributes)
        $('#match').on('change', function() {
            const $selected = $(this).find('option:selected');
            const $teamSelect = $('#team_names');

            $teamSelect.empty().append('<option value="">Select Team</option>');

            if ($(this).val()) {
                const teams = [{
                        id: $selected.data('ta-id'),
                        name: $selected.data('ta-name')
                    },
                    {
                        id: $selected.data('tb-id'),
                        name: $selected.data('tb-name')
                    }
                ];

                $.each(teams, function(i, team) {
                    if (team.id) {
                        $teamSelect.append(`<option value="${team.id}">${team.name}</option>`);
                    }
                });
            }
        });

        $('#match').on('change', function() {
            const matchId = $(this).val();
            if (matchId) {
                $('#pay_to').removeClass('locked-step'); // UNLOCK
            } else {
                $('#pay_to').addClass('locked-step').val('');
            }
        });

        $('#official_type').on('change', function() {
            const typeId = $(this).val();
            const matchId = $('#match').val();
            const $nameSelect = $('#official_name');

            $nameSelect.addClass('locked-step').html('<option value="">Loading...</option>');

            // Validation: Ensure a match is selected first
            if (!matchId) {
                alert("Please select a Match first!");
                $(this).val(''); // Reset type selection
                return;
            }

            // Reset dropdown
            $nameSelect.html('<option value="">Loading...</option>');

            if (!typeId) {
                $nameSelect.html('<option value="">-- Select Type First --</option>');
                return;
            }

            $.ajax({
                url: '/finance/voucher/getOfficialsByType',
                type: 'POST',
                data: {
                    type_id: typeId,
                    match_id: matchId,
                    "<?= csrf_token() ?>": $('#csrf_token_value').val()
                },
                dataType: 'json',
                success: function(data) {
                    let options = '<option value="">Select Name</option>';
                    if (data.length > 0) {
                        $.each(data, function(index, official) {
                            options += `<option value="${official.id}">${official.name}</option>`;
                        });
                        $nameSelect.html(options).removeClass('locked-step');
                    } else {
                        options = '<option value="">No officials found</option>';
                    }
                    $nameSelect.html(options);
                },
                error: function() {
                    alert('Error fetching officials.');
                }
            });
        });

    });

    $('#pay_to').on('change', function() {
        const payTo = $(this).val();
        $('#team_names, #official_type, #official_name').addClass('locked-step').val('');

        if (payTo === 'Teams') {
            const $selectedMatch = $('#match option:selected');
            let options = '<option value="">Select Team</option>';
            options += `<option value="${$selectedMatch.data('ta-id')}">${$selectedMatch.data('ta-name')}</option>`;
            options += `<option value="${$selectedMatch.data('tb-id')}">${$selectedMatch.data('tb-name')}</option>`;
            
            $('#team_names').html(options).removeClass('locked-step');
        } else if (payTo === 'Officials') {
            $('#official_type').removeClass('locked-step');
        }
    }); // Run once on page load


    $('#voucher_date').on('change', function() {
        const voucher_date = $(this).val();
        if (voucher_date) {
            $('#payment_mode').removeClass('locked-step'); // UNLOCK
        } else {
            $('#payment_mode').addClass('locked-step').val('');
        }
    });

    $('#payment_mode').on('change', function() {
        const payment_mode = $(this).val();
        if (payment_mode) {
            $('#transaction_type').removeClass('locked-step');
            $('#ledger_head').removeClass('locked-step');
        } else {
            $('#transaction_type').addClass('locked-step').val('');
            $('#ledger_head').addClass('locked-step').val('');
        }
    });



    $('#transaction_type').on('change', function() {
        const selectedType = $(this).val(); // This will be 'Dr' or 'Cr'
        const $ledgerSelect = $('#ledger_head');
        const paymentType = $('#payment_mode').val();

        // Reset the ledger dropdown
        $ledgerSelect.val('');

        // Hide all options first
        $ledgerSelect.find('option').hide();

        // Show the default "Select" option
        $ledgerSelect.find('option[value=""]').show().text(selectedType ? '-- Select ' + selectedType + ' Head --' : '-- Select Type First --');

        if (selectedType) {
            if (selectedType === 'Dr') {
                // DEBIT SIDE: Filter by Group based on Payment Mode
                if (paymentType == 'Cash') {
                    $ledgerSelect.find('option[data-type="G7"]').show();
                } else if (paymentType == 'Bank') {
                    $ledgerSelect.find('option[data-type="G4"]').show();
                }
            } else {
                // CREDIT SIDE: Show everything EXCEPT the Cash and Bank groups
                // Note: We use data-type to match your Dr logic
                $ledgerSelect.find('option')
                    .not('[data-type="G7"], [data-type="G4"], [value=""]')
                    .show();
            }
        }
    });

    $('#bank_account').on('change', function() {
        // Get the selected option
        const selectedOption = $(this).find('option:selected');

        // Extract the IFSC from the data attribute
        const ifsc = selectedOption.data('ifsc');

        // Find the input named 'bank_ifsc' and set its value
        $('input[name="bank_ifsc"]').val(ifsc || '');
    });

    $('#payment_mode').on('change', function() {
        const mode = $(this).val();
        const $bankSection = $('#bank_details_section');
        const $refSection = $('#ref_no_section');
        const $paythrghSection = $('#payment_thrgh_section');

        if (mode === 'Bank') {
            $bankSection.fadeIn();
            $refSection.fadeIn();
            $paythrghSection.fadeIn();
        } else {
            // Mode is Cash - hide extra fields
            $bankSection.hide();
            $refSection.hide();
            $paythrghSection.hide();
        }
    });

    $('#payment_thrgh').on('change', function() {
        const mode = $(this).val();
        const $refLabel = $('#ref_label');

        // Change label text based on mode
        if (mode === 'NEFT' || mode === 'RTGS') {
            $refLabel.text('UTR / Transaction ID');
        } else {
            $refLabel.text('Cheque Number');
        }
    });

    let slno = 1;

    $('#add_row').on('click', function() {
        const type = $('#transaction_type').val();
        const ledgerId = $('#ledger_head').val();
        const ledgerName = $("#ledger_head option:selected").text();
        const narr = $('#narrations').val();
        const amount = parseFloat($('input[name="amount"]').val()) || 0;

        if (!ledgerId || amount <= 0) {
            alert("Please select a ledger and enter a valid amount.");
            return;
        }

        // Locks the dropdown visually and functionally but keeps it "enabled" for POST
        $('#voucher_date').css({
            'pointer-events': 'none',
            'opacity': '0.7',
            'background-color': '#e9ecef' // Makes it look like a read-only field
        });

        $('#payment_mode').css({
            'pointer-events': 'none',
            'opacity': '0.7',
            'background-color': '#e9ecef' // Makes it look like a read-only field
        });
        // --------------------------

        // Calculate next SL NO based on current rows
        const nextSl = $('#voucher_body tr').length + 1;

        const drVal = (type === 'Dr') ? amount.toFixed(2) : '0.00';
        const crVal = (type === 'Cr') ? amount.toFixed(2) : '0.00';

        const row = `
        <tr>
            <td class="sl-no">${nextSl}</td>
            <td>
                ${ledgerName}<br>
                Narr. - ${narr}
                <input type="hidden" name="items[ledger_id][]" value="${ledgerId}">
                <input type="hidden" name="items[narr][]" value="${narr}">
            </td>
            <td class="dr-col">${drVal}</td>
            <td class="cr-col">${crVal}</td>
            <td>
                <button type="button" class="btn btn-outline-danger btn-sm remove-row">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
            <input type="hidden" name="items[dr][]" value="${drVal}">
            <input type="hidden" name="items[cr][]" value="${crVal}">
        </tr>`;

        $('#voucher_body').append(row);
        calculateVoucherTotals();
        $('input[name="amount"]').val('');
    });

    // Remove Row
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        calculateVoucherTotals();
    });

    function calculateVoucherTotals() {
        let sumDr = 0;
        let sumCr = 0;

        // Count current rows in the table
        let rowCount = $('#voucher_body tr').length;

        $('.dr-col').each(function() {
            sumDr += parseFloat($(this).text()) || 0;
        });
        $('.cr-col').each(function() {
            sumCr += parseFloat($(this).text()) || 0;
        });

        // --- UNLOCK IF TABLE IS EMPTY ---
        if (rowCount === 0) {
            $('#payment_mode').css({
                'pointer-events': 'auto',
                'opacity': '1',
                'background-color': '#fff'
            });
            $('#voucher_date').css({
                'pointer-events': 'auto',
                'opacity': '1',
                'background-color': '#fff'
            });
        }
        // ----------------------

        $('#total_dr').text(sumDr.toFixed(2));
        $('#total_cr').text(sumCr.toFixed(2));

        const diff = Math.abs(sumDr - sumCr);
        const $status = $('#balance_status');
        const $saveBtn = $('#final_save_btn');

        if (sumDr > 0 && sumDr === sumCr) {
            $status.removeClass('bg-danger').addClass('bg-success').text('Balanced');
            $saveBtn.prop('disabled', false);
        } else {
            $status.removeClass('bg-success').addClass('bg-danger').text('Unbalanced: Diff ₹' + diff.toFixed(2));
            $saveBtn.prop('disabled', true);
        }
    }

    function resetSerialNumbers() {
        $('#voucher_body tr').each(function(index) {
            $(this).find('.sl-no').text(index + 1);
        });
    }

    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();

        // Recalculate everything
        resetSerialNumbers();
        calculateVoucherTotals();
    });
    s
</script>
<?= $this->endSection() ?>