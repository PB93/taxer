<?php
namespace app;
use Exception;

class Taxer
{
    public function processFile($filePath): void
    {
        $fileContents = file_get_contents($filePath);

        foreach (explode("\n", $fileContents) as $row) {
            if (empty($row)) continue;
            $amountFixed = $this->processRow($row);
            echo round($amountFixed, 2);
            print "\n";
        }
    }

    /**
     * @throws Exception
     */
    public function processRow($row): float
    {
        $p = explode(",", $row);
        [$cardNumber, $amount, $currency] = [
            trim(explode(":", $p[0])[1], '"'),
            trim(explode(":", $p[1])[1], '"'),
            trim(explode(":", $p[2])[1], '"}'),
        ];

        $cardData = $this->getCardData($cardNumber);
        $isEu = $cardData ? $this->isEu($cardData->country->alpha2) : false;
        $rate = $this->getExchangeRate($currency);

        return $this->calculateAmount($amount, $currency, $rate, $isEu);
    }

    /**
     * @throws Exception
     */
    public function getCardData($bin)
    {
        $binResults = file_get_contents('https://lookup.binlist.net/' . $bin);
        if (!$binResults) {
            throw new Exception('Error fetching BIN data!');
        }
        return json_decode($binResults);
    }

    public function getExchangeRate($currency, $baseCurrency = 'EUR')
    {
        $curl = curl_init();
        $symbols = "EUR,GBP,USD,JPY";

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.apilayer.com/exchangerates_data/latest?symbols=$symbols&base=$baseCurrency",
            CURLOPT_HTTPHEADER => array(
                "Content-Type: text/plain",
                "apikey: 6J1QU4mqsOxHEM99Dax1zABXcpEePxET"
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = @json_decode($response);

        return $response->rates->$currency ?? 0;
    }

    public function calculateAmount($amount, $currency, $rate, $isEu): float
    {
        if ($currency !== 'EUR' || $rate > 0) {
            $amount = $amount / $rate;
        }

        return $amount * ($isEu ? 0.01 : 0.02);
    }

    public function isEu($countryCode): bool
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU',
            'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];
        return in_array($countryCode, $euCountries);
    }
}

