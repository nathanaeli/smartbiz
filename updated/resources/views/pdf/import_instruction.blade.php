<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Import Instructions</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
            font-size: 14px;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #6366f1;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #6366f1;
        }

        .section {
            margin-bottom: 25px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            background-color: #f3f4f6;
            padding: 8px;
            border-left: 4px solid #6366f1;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .content-table th,
        .content-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        .content-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .swahili {
            font-style: italic;
            color: #555;
            display: block;
            margin-top: 4px;
        }

        .note {
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo">STOCKFLOWKP</div>
        <div>Sales Import Guide / Mwongozo wa Kuingiza Mauzo</div>
    </div>

    <div class="section">
        <div class="title">1. How to Fill the Template / Jinsi ya kujaza</div>
        <table class="content-table">
            <thead>
                <tr>
                    <th width="30%">Column / Safu</th>
                    <th>Instruction / Maelekezo</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Quantity Sold</strong><br><span class="swahili">Kiasi Kilichouzwa</span></td>
                    <td>
                        Enter the number of items sold.<br>
                        <span class="swahili">Weka idadi ya bidhaa zilizouzwa.</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Buying Price (Cost)</strong><br><span class="swahili">Bei ya Kununua</span></td>
                    <td>
                        <strong>CRITICAL:</strong> Enter the unit cost price at the time of purchase.<br>
                        <span class="swahili"><strong>MUHIMU:</strong> Weka bei ya kununua ya bidhaa kwa wakati huo.</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Sale Date</strong><br><span class="swahili">Tarehe ya Mauzo</span></td>
                    <td>
                        Format: YYYY-MM-DD (e.g., 2023-12-25). If empty, today's date is used.<br>
                        <span class="swahili">Mfumo: YYYY-MM-DD. Ikiachwa wazi, tarehe ya leo itatumika.</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Customer Name/Phone</strong><br><span class="swahili">Jina/Simu ya Mteja</span></td>
                    <td>
                        Fill to link sale to a customer history.<br>
                        <span class="swahili">Jaza ili kuunganisha mauzo na historia ya mteja.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="title">2. System Impact / Matokeo kwenye Mfumo</div>
        <table class="content-table">
            <tbody>
                <tr>
                    <td width="30%"><strong>Audit Trail (Virtal Stock)</strong><br><span class="swahili">Ukaguzi (Stock)</span></td>
                    <td>
                        The system records a "Stock In" using the <strong>Buying Price</strong> you entered, then immediately records the Sale. This ensures Profit/Loss is calculated using the EXACT cost you provided.<br>
                        <span class="swahili">Mfumo unarekodi "Kuingia kwa Stock" ukitumia <strong>Bei ya Kununua</strong> uliyoweka, kisha unarekodi Mauzo papo hapo. Hii inahakikisha Faida/Hasara inakokotolewa kwa kutumia gharama SAHIHI.</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Transactions</strong><br><span class="swahili">Miamala</span></td>
                    <td>
                        An "Income" transaction is created automatically (unless it is a Loan).<br>
                        <span class="swahili">Muamala wa "Mapato" unatengenezwa kiotomatiki (isipokuwa kama ni Mkopo).</span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Inventory</strong><br><span class="swahili">Mali Ghafi</span></td>
                    <td>
                        Since we add virtual stock and then sell it, your <strong>Current Stock Count</strong> remains unchanged.<br>
                        <span class="swahili">Kwa kuwa tunaongeza stock na kuiuza papo hapo, <strong>Idadi ya Stock Iliyopo</strong> haibadiliki.</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="note">
        <strong>Note:</strong> Always save your Excel file before uploading. Ensure dates are correct.<br>
        <span class="swahili"><strong>Zingatia:</strong> Hifadhi faili yako ya Excel kabla ya kupakia. Hakikisha tarehe ni sahihi.</span>
    </div>

</body>

</html>