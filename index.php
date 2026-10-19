
<?php
// SWE40006 - PHP Expense Splitter
// Server-side bill splitting calculator

$totalInput = $_POST['total'] ?? '600000';
$peopleInput = $_POST['people'] ?? '4';
$tipInput = $_POST['tip'] ?? '10';

$error = '';
$result = null;

// Escape output to prevent HTML injection
function safe($value): string {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Format amounts in Vietnamese Dong
function money(float $amount): string {
    return number_format($amount, 0, '.', ',') . ' VND';
}

// Process the submitted form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $total = filter_var(
        $totalInput,
        FILTER_VALIDATE_FLOAT
    );

    $people = filter_var(
        $peopleInput,
        FILTER_VALIDATE_INT
    );

    $tip = filter_var(
        $tipInput,
        FILTER_VALIDATE_FLOAT
    );

    // Server-side input validation
    if ($total === false || $total <= 0 ||
        $total > 1000000000) {
        $error = 'Please enter a valid bill amount.';
    } elseif ($people === false ||
              $people < 1 || $people > 100) {
        $error = 'Number of people must be 1 to 100.';
    } elseif ($tip === false ||
              $tip < 0 || $tip > 100) {
        $error = 'Tip percentage must be 0 to 100.';
    } else {
        // Calculate tip and individual payment
        $tipAmount = $total * $tip / 100;
        $grandTotal = $total + $tipAmount;
        $perPerson = $grandTotal / $people;

        $result = [
            'bill' => $total,
            'tip' => $tipAmount,
            'grand' => $grandTotal,
            'each' => $perPerson
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Expense Splitter | SWE40006</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 540px;
            margin: 45px auto;
        }

        .card {
            background: white;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 10px 35px #00000012;
        }

        h1 {
            color: #0f766e;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 18px 0 8px;
        }

        input {
            display: block;
            width: 100%;
            padding: 13px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 16px;
        }

        input:focus {
            outline: 2px solid #0d9488;
            border-color: transparent;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            border: none;
            border-radius: 9px;
            background: #0f766e;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background: #115e59;
        }

        .result {
            margin-top: 26px;
            background: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 12px;
            padding: 20px;
        }

        .result h2 {
            margin-top: 0;
            color: #0f766e;
        }

        .detail {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px solid #ccfbf1;
        }

        .amount {
            margin-top: 20px;
            text-align: center;
        }

        .amount strong {
            display: block;
            color: #0f766e;
            font-size: 29px;
            margin-top: 8px;
        }

        .error {
            margin-top: 20px;
            padding: 14px;
            border-radius: 8px;
            background: #fef2f2;
            color: #b91c1c;
        }

        footer {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 22px;
        }

        @media (max-width: 600px) {
            .container { margin: 10px auto; }
            .card { padding: 22px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h1>Expense Splitter</h1>
        <p class="subtitle">
            Split your bill quickly and fairly
        </p>

        <form method="POST" action="">
            <label for="total">
                Total Bill (VND)
            </label>
            <input
                id="total"
                type="number"
                name="total"
                min="1"
                max="1000000000"
                step="1"
                value="<?= safe($totalInput) ?>"
                required
            >

            <label for="people">
                Number of People
            </label>
            <input
                id="people"
                type="number"
                name="people"
                min="1"
                max="100"
                step="1"
                value="<?= safe($peopleInput) ?>"
                required
            >

            <label for="tip">
                Tip Percentage (%)
            </label>
            <input
                id="tip"
                type="number"
                name="tip"
                min="0"
                max="100"
                step="0.1"
                value="<?= safe($tipInput) ?>"
                required
            >

            <button type="submit">
                Calculate Split
            </button>
        </form>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?= safe($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($result !== null): ?>
            <div class="result">
                <h2>Bill Summary</h2>

                <div class="detail">
                    <span>Original Bill</span>
                    <strong>
                        <?= money($result['bill']) ?>
                    </strong>
                </div>

                <div class="detail">
                    <span>Tip Amount</span>
                    <strong>
                        <?= money($result['tip']) ?>
                    </strong>
                </div>

                <div class="detail">
                    <span>Final Total</span>
                    <strong>
                        <?= money($result['grand']) ?>
                    </strong>
                </div>

                <div class="amount">
                    Each Person Pays
                    <strong>
                        <?= money($result['each']) ?>
                    </strong>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        SWE40006 | Software Deployment and Evolution
    </footer>
</div>

</body>
</html>
