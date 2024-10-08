<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentMethodsController extends Controller
{

    private $paymentMethods = [
        [
            'id' => '1',
            'storeId' => 'a2e21117-943d-4297-9321-fb2bd851b03e',
            'name' => 'Unzer Bank Transfer',
            'subtitle' => 'Payment Method 1',
            'isEnabled' => true,
            'image' => '<svg width="92" height="57" viewBox="0 0 92 57" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="92" height="57" fill="url(#pattern0_2535_629)"/>
                        <defs>
                        <pattern id="pattern0_2535_629" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_2535_629" transform="matrix(0.00413043 0 0 0.00666667 -0.00184783 0)"/>
                        </pattern>
                        <image id="image0_2535_629" width="243" height="150" xlink:href="data:image/png;base64,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"/>
                        </defs>
                        </svg>',
            'configuration' => [
                'paymentMethodName' => 'Unzer payments',
                'paymentMethodDescription' => ['language' => 'de', 'value' => 'Unzer payments'],
                'bookingMethod' => 'charge',
                'chargeOnStatusChange' => 'Awaiting payment',
                'orderAmount' => ['minAmount' => 19, 'maxAmount' => 20000],
                'surcharge' => 3,
                'restrictedCountries' => ['Germany', 'France'],
            ]
        ],
        [
            'id' => '2',
            'storeId' => 'a2e21117-943d-4297-9321-fb2bd851b03e',
            'name' => 'Apple pay',
            'subtitle' => 'Payment Method 2',
            'isEnabled' => true,
            'image' => '<svg width="51" height="50" viewBox="0 0 51 50" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="51" height="50" fill="url(#pattern0_2535_673)"/>
                        <defs>
                        <pattern id="pattern0_2535_673" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_2535_673" transform="scale(0.0196078 0.02)"/>
                        </pattern>
                        <image id="image0_2535_673" width="51" height="50" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADMAAAAyCAYAAADx/eOPAAADp0lEQVRoBe1XSyh1URS+U8mjPIpCSCQUE6UkpZQyYEBhYuCRQhETyjWQMiZmRiYGRopMJHmkJAaiSJFHDDxCUlp/3/rbp73P3ee693a7/+5vnzqdtfdae631rW+dde71EZGf/o/L77NgzGTSMmMmL0SWGctMDCpg2ywGRY4ohGUmorLF4JBlJgZFjiiEZSaissXgUPjM/Pz80NfXVwxyCztEeGAODg4oJSWF5ufntZFeXl5oc3NTuXd2duj19VVrH+XN8MBkZGRQXFwcPTw8aPPY3t4mn8+nvVtaWujp6Ul7LkqboYO5u7ujmpoaWl1d9YwtwNTW1jrszM3NUVFREQNsbGz0PBsFhTcYVLGnp4dSU1MpMTGRBgcHqa+vj2VUv7Kykra2tpQcBJjW1lZl/+3tjcAqzt3e3iq6KC68wVRVVWnbRW6j7u5uJRcvMDBqa2tjf+vr63xmZWWFmY6Pj6esrCzq6Oig8/Nz1jU0NLAt3jf56u/v5/3T01N5W8h6MFdXV78CKS4uFk6cZzAwaD0UAkNkY2OD5dLSUpqYmOAOgC47O5s+Pj5oaWmJ9SMjI45vCACdlJSk7EkLPZjd3d1fwYyOjkp+/opeYBYWFthfcnIyfX5+0tTUFLPy+Pjo+EAbAxDeSQDCoCkoKHD0x8fHrO/s7HT2XELkYNrb212+iASYtLQ0ThYDA++caM3l5WXlzOXlpTMo8D7CbnFxkW3QdlifnZ3x2u/38xqselx6MOhdkYDXE5U7OjpS/OrA1NfX0/DwMMl9jurn5ORoYwgwa2trrJ+ZmeEY5eXllJ6ersRzLfRgvr+/mWYvIGI/ISGB7u/vHZ8CjHuaOQZE9P7+zkmCscPDQ0c1PT3N+wIMFGC4urqabm5uWDcwMODYawQ9GBh2dXWxA5G47llWVqb4DAXM9fU1+62rq1POYtQjhgxGTK/JyUnW7e/vK2dcC28w+AlSWFjoCQjfnpOTE8VfKGBwoKKigv329vYSqp2Xl0eZmZkBYJC8KGJubq4SS7PwBgNjtEFJSQlhCg0NDdH4+Djl5+fzB3Bvby/AX6hgLi4uqLm5mdCmSBZtOTs7GwAGARAPNmNjYwHxXBvBwbiM/8lSDAox1YIkYS4Y/PoW70xTU1MQDI7KXDDiXcFXX56YTuqBgrlgnp+fCTf+DIZ4mQsmRACymQUjV8Mk2TJjEhtyLpYZuRomyZYZk9iQc7HMyNUwSbbMmMSGnItlRq6GSfL/xcwfMXP5VFE32C0AAAAASUVORK5CYII="/>
                        </defs>
                        </svg>',
            'configuration' => [
                'paymentMethodName' => 'Unzer payments',
                'paymentMethodDescription' => ['language' => 'de', 'value' => 'Unzer payments'],
                'bookingMethod' => 'charge',
                'chargeOnStatusChange' => 'Awaiting payment',
                'orderAmount' => ['minAmount' => 19, 'maxAmount' => 20000],
                'surcharge' => 3,
                'restrictedCountries' => ['Germany', 'France'],
            ]
        ],
        [
            'id' => '2',
            'storeId' => 'a2e21117-943d-4297-9321-fb2bd851b03e',
            'name' => 'Apple pay',
            'subtitle' => 'Payment Method 2',
            'isEnabled' => true,
            'image' => '<svg width="51" height="50" viewBox="0 0 51 50" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="51" height="50" fill="url(#pattern0_2535_673)"/>
                        <defs>
                        <pattern id="pattern0_2535_673" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_2535_673" transform="scale(0.0196078 0.02)"/>
                        </pattern>
                        <image id="image0_2535_673" width="51" height="50" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADMAAAAyCAYAAADx/eOPAAADp0lEQVRoBe1XSyh1URS+U8mjPIpCSCQUE6UkpZQyYEBhYuCRQhETyjWQMiZmRiYGRopMJHmkJAaiSJFHDDxCUlp/3/rbp73P3ee693a7/+5vnzqdtfdae631rW+dde71EZGf/o/L77NgzGTSMmMmL0SWGctMDCpg2ywGRY4ohGUmorLF4JBlJgZFjiiEZSaissXgUPjM/Pz80NfXVwxyCztEeGAODg4oJSWF5ufntZFeXl5oc3NTuXd2duj19VVrH+XN8MBkZGRQXFwcPTw8aPPY3t4mn8+nvVtaWujp6Ul7LkqboYO5u7ujmpoaWl1d9YwtwNTW1jrszM3NUVFREQNsbGz0PBsFhTcYVLGnp4dSU1MpMTGRBgcHqa+vj2VUv7Kykra2tpQcBJjW1lZl/+3tjcAqzt3e3iq6KC68wVRVVWnbRW6j7u5uJRcvMDBqa2tjf+vr63xmZWWFmY6Pj6esrCzq6Oig8/Nz1jU0NLAt3jf56u/v5/3T01N5W8h6MFdXV78CKS4uFk6cZzAwaD0UAkNkY2OD5dLSUpqYmOAOgC47O5s+Pj5oaWmJ9SMjI45vCACdlJSk7EkLPZjd3d1fwYyOjkp+/opeYBYWFthfcnIyfX5+0tTUFLPy+Pjo+EAbAxDeSQDCoCkoKHD0x8fHrO/s7HT2XELkYNrb212+iASYtLQ0ThYDA++caM3l5WXlzOXlpTMo8D7CbnFxkW3QdlifnZ3x2u/38xqselx6MOhdkYDXE5U7OjpS/OrA1NfX0/DwMMl9jurn5ORoYwgwa2trrJ+ZmeEY5eXllJ6ersRzLfRgvr+/mWYvIGI/ISGB7u/vHZ8CjHuaOQZE9P7+zkmCscPDQ0c1PT3N+wIMFGC4urqabm5uWDcwMODYawQ9GBh2dXWxA5G47llWVqb4DAXM9fU1+62rq1POYtQjhgxGTK/JyUnW7e/vK2dcC28w+AlSWFjoCQjfnpOTE8VfKGBwoKKigv329vYSqp2Xl0eZmZkBYJC8KGJubq4SS7PwBgNjtEFJSQlhCg0NDdH4+Djl5+fzB3Bvby/AX6hgLi4uqLm5mdCmSBZtOTs7GwAGARAPNmNjYwHxXBvBwbiM/8lSDAox1YIkYS4Y/PoW70xTU1MQDI7KXDDiXcFXX56YTuqBgrlgnp+fCTf+DIZ4mQsmRACymQUjV8Mk2TJjEhtyLpYZuRomyZYZk9iQc7HMyNUwSbbMmMSGnItlRq6GSfL/xcwfMXP5VFE32C0AAAAASUVORK5CYII="/>
                        </defs>
                        </svg>',
            'configuration' => [
                'paymentMethodName' => 'Unzer payments',
                'paymentMethodDescription' => ['language' => 'de', 'value' => 'Unzer payments'],
                'bookingMethod' => 'charge',
                'chargeOnStatusChange' => 'Awaiting payment',
                'orderAmount' => ['minAmount' => 19, 'maxAmount' => 20000],
                'surcharge' => 3,
                'restrictedCountries' => ['Germany', 'France'],
            ]
        ],
        [
            'id' => '3',
            'storeId' => 'a2e21117-943d-4297-9321-fb2bd851b03e',
            'name' => 'iDeal',
            'subtitle' => 'Payment Method 3',
            'isEnabled' => false,
            'image' => '<svg width="50" height="51" viewBox="0 0 50 51" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="50" height="51" fill="url(#pattern0_2535_681)"/>
                        <defs>
                        <pattern id="pattern0_2535_681" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_2535_681" transform="scale(0.02 0.0196078)"/>
                        </pattern>
                        <image id="image0_2535_681" width="50" height="51" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAzCAYAAADVY1sUAAAE6ElEQVRoBe2Yf0xbVRTHMSaaufFjM27qZD+ASWL8x81BYDNE3Q81hMT94xgzmYoY5qLZFIYsqyZjWwYYiEI2UIw/NtwQ6DYVcTAIYsA4gtlgUHCOEmGlFELpoLS05WvOad5LKULf61rWkd7kvnfeefeeez733F/vBQA4txByABZI8oP4WiD9EfFHxEs94B9aXupYt836I+J213mpoqSIZGUdgadyWVkZ1Gq1x3EkgTy0eBGe3bgB27ZvdStv3bYF6zc8g9VrVmFJ4GKQvRWPLkdsbAyOHT+GoaGhOwaTDFJV9fMdN0YGbDYbVCoVysvLkfHRQYSuegLLHl4KheIw9Hq9223MO4izp2azGaWlpYhYF4E1a1ej+tdq5yKSnj0KYlC24fp9B2bP93+Am1F5GEj/EberOmAbM4tOTkxM4NChTAQGLYFSWSnqpQrzC+IE2fFAGvpePwNTm0b099SpkzyPmpqaRJ0U4a6CCNEjIP23V0R/D2akIzwiHEajUdS5EnwCRAAaLb/K/hoMBjy+8jEUFHzuyn/xvVsgtPK0trZCq9WKhkhwOUechpYAINw7gzNh6R9lm/n5+QgLWwuLxTKtjdkeZIN0d3cjMjISAQEBnFNSUmC1WkX7U5NWjF3uxo2ns6FaoRD1JKi3F/FC0P92Gestt0bFhcHcqcVwXgM07yv5nWZAw/uN1LkiC4QiERoaKkIIMAqF3WFyZvDjakz2DMPYrIZquV0/eu4v1neHZbHjhgvtYs//s/5T1gkgHYvSYTOYGCY6OgpHj2ax7OoiC4SiITjveI+JieF2hKHVv+d7fibHHdONp06AJvaUcRIDaRfZ4UHFL9NAaJiNXeriavsP7MfOxJ2OJmaVZYGMjIz8L0h8fDw3IIAMfHgRsNrQtfIT1o+etUdE9chh9L5UxDrT1X4GMf6hngGiO17LZXJyc7D5uc0su7rIAiFjiYmJM2Bqa+0NW3VjGK//G1MTFgx/9ps4R0ztGtb3vvIFhgt+h+XfER5q+u9agKkpHoI0tEhP9c1dg+z36TOneRl2BUHvZYPQkaKwsBAJCQlITU1Fc3Oz2A6B0LDQZvzEvdwZksmOkXOUe18uhv6bK6CI0RCiOUT6nrgC1gvlTNcH2Ob580osXRYi2p9LkA0ylzFhaAnLqbv3/rfOcjMlJV8iLDxsribFd7JBcnNzERcXNy23tLSwQU+BaDPtJ+3snGzQyiUlyQZJTk6eMUdqamo8CqIvbWV7e/emYseOV6VwyJ8j8wFi1d5m5+lon5efd2+CCMtze3s77+x0l5J8bmjRvkJpV9IuREVvlMLAZXwKRHfkEjvV0NDA0airq7u3QDoeTMPQicvsNJ2oaclN2p0kGYIK3tWI0Lnr1js/8I5OzoyPj+OFF5/Hpk2xLMshkQ0y1z4y3niTd2naqWfL6i0noXlPiZGv/4RFYxB9rays4O8P+kWk0+lEvVRBNohUw1LK0adsyVclvOnR/y76CSj1Q8rZvmQQ2mVpErqT6+vrUVFRgeLiIv4h98abe3hFCgoORHBIEPbtexc9PT3Ovsl6lgxCfwc9ldc9GQGCobMUfQl6IkkCcScKjnUaGxtxre0a+vr6YDLZv/484byjDUkgjhV8VfaD+Fpk/BHxR8RLPeAfWl7qWLfN+iPidtd5qSJF5LWFkP8DvItTnyMFivcAAAAASUVORK5CYII="/>
                        </defs>
                        </svg>',
            'configuration' => [
                'paymentMethodName' => 'Unzer payments',
                'paymentMethodDescription' => ['language' => 'de', 'value' => 'Unzer payments'],
                'bookingMethod' => 'charge',
                'chargeOnStatusChange' => 'Awaiting payment',
                'orderAmount' => ['minAmount' => 19, 'maxAmount' => 20000],
                'surcharge' => 3,
                'restrictedCountries' => ['Germany', 'France'],
            ]
        ],
        [
            'id' => '4',
            'storeId' => 'e2bb77b0-6021-4723-913a-e0c0fe87afb1',
            'name' => 'iDeal',
            'subtitle' => 'Payment Method 3',
            'isEnabled' => true,
            'image' => '<svg width="50" height="51" viewBox="0 0 50 51" fill="none" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                        <rect width="50" height="51" fill="url(#pattern0_2535_681)"/>
                        <defs>
                        <pattern id="pattern0_2535_681" patternContentUnits="objectBoundingBox" width="1" height="1">
                        <use xlink:href="#image0_2535_681" transform="scale(0.02 0.0196078)"/>
                        </pattern>
                        <image id="image0_2535_681" width="50" height="51" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADIAAAAzCAYAAADVY1sUAAAE6ElEQVRoBe2Yf0xbVRTHMSaaufFjM27qZD+ASWL8x81BYDNE3Q81hMT94xgzmYoY5qLZFIYsqyZjWwYYiEI2UIw/NtwQ6DYVcTAIYsA4gtlgUHCOEmGlFELpoLS05WvOad5LKULf61rWkd7kvnfeefeeez733F/vBQA4txByABZI8oP4WiD9EfFHxEs94B9aXupYt836I+J213mpoqSIZGUdgadyWVkZ1Gq1x3EkgTy0eBGe3bgB27ZvdStv3bYF6zc8g9VrVmFJ4GKQvRWPLkdsbAyOHT+GoaGhOwaTDFJV9fMdN0YGbDYbVCoVysvLkfHRQYSuegLLHl4KheIw9Hq9223MO4izp2azGaWlpYhYF4E1a1ej+tdq5yKSnj0KYlC24fp9B2bP93+Am1F5GEj/EberOmAbM4tOTkxM4NChTAQGLYFSWSnqpQrzC+IE2fFAGvpePwNTm0b099SpkzyPmpqaRJ0U4a6CCNEjIP23V0R/D2akIzwiHEajUdS5EnwCRAAaLb/K/hoMBjy+8jEUFHzuyn/xvVsgtPK0trZCq9WKhkhwOUechpYAINw7gzNh6R9lm/n5+QgLWwuLxTKtjdkeZIN0d3cjMjISAQEBnFNSUmC1WkX7U5NWjF3uxo2ns6FaoRD1JKi3F/FC0P92Gestt0bFhcHcqcVwXgM07yv5nWZAw/uN1LkiC4QiERoaKkIIMAqF3WFyZvDjakz2DMPYrIZquV0/eu4v1neHZbHjhgvtYs//s/5T1gkgHYvSYTOYGCY6OgpHj2ax7OoiC4SiITjveI+JieF2hKHVv+d7fibHHdONp06AJvaUcRIDaRfZ4UHFL9NAaJiNXeriavsP7MfOxJ2OJmaVZYGMjIz8L0h8fDw3IIAMfHgRsNrQtfIT1o+etUdE9chh9L5UxDrT1X4GMf6hngGiO17LZXJyc7D5uc0su7rIAiFjiYmJM2Bqa+0NW3VjGK//G1MTFgx/9ps4R0ztGtb3vvIFhgt+h+XfER5q+u9agKkpHoI0tEhP9c1dg+z36TOneRl2BUHvZYPQkaKwsBAJCQlITU1Fc3Oz2A6B0LDQZvzEvdwZksmOkXOUe18uhv6bK6CI0RCiOUT6nrgC1gvlTNcH2Ob580osXRYi2p9LkA0ylzFhaAnLqbv3/rfOcjMlJV8iLDxsribFd7JBcnNzERcXNy23tLSwQU+BaDPtJ+3snGzQyiUlyQZJTk6eMUdqamo8CqIvbWV7e/emYseOV6VwyJ8j8wFi1d5m5+lon5efd2+CCMtze3s77+x0l5J8bmjRvkJpV9IuREVvlMLAZXwKRHfkEjvV0NDA0airq7u3QDoeTMPQicvsNJ2oaclN2p0kGYIK3tWI0Lnr1js/8I5OzoyPj+OFF5/Hpk2xLMshkQ0y1z4y3niTd2naqWfL6i0noXlPiZGv/4RFYxB9rays4O8P+kWk0+lEvVRBNohUw1LK0adsyVclvOnR/y76CSj1Q8rZvmQQ2mVpErqT6+vrUVFRgeLiIv4h98abe3hFCgoORHBIEPbtexc9PT3Ovsl6lgxCfwc9ldc9GQGCobMUfQl6IkkCcScKjnUaGxtxre0a+vr6YDLZv/484byjDUkgjhV8VfaD+Fpk/BHxR8RLPeAfWl7qWLfN+iPidtd5qSJF5LWFkP8DvItTnyMFivcAAAAASUVORK5CYII="/>
                        </defs>
                        </svg>',
            'configuration' => [
                'paymentMethodName' => 'Unzer payments',
                'paymentMethodDescription' => ['language' => 'de', 'value' => 'Unzer payments'],
                'bookingMethod' => 'charge',
                'chargeOnStatusChange' => 'Awaiting payment',
                'orderAmount' => ['minAmount' => 19, 'maxAmount' => 20000],
                'surcharge' => 3,
                'restrictedCountries' => ['Germany', 'France'],
            ]
        ]
    ];

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function getPaymentMethods(Request $request): JsonResponse
    {
        $storeId = $request->input('storeId');

        return response()->json(collect($this->paymentMethods)->filter(function ($paymentMethod) use ($storeId) {
            return $paymentMethod['storeId'] === $storeId;
        })->values()->toArray());
    }


}
