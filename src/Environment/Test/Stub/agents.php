<?php
/**
 * Part of Windwalker project.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

use Windwalker\Environment\Browser\Browser;

return [

    // Edge Windows
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_EDGE_HTML,
        Browser::EDGE,
        '12.246',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246',
    ],

    // Edge Windows Phone
    [
        Browser::DEVICE_WINDOWS_PHONE,
        true,
        Browser::ENGINE_EDGE_HTML,
        Browser::EDGE,
        '12',
        'Mozilla/5.0 (Windows Phone 10.0; Android 4.2.1; DEVICE INFO) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.71 Mobile Safari/537.36 Edge/12.0',
    ],

    // Edge Chromium
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_BLINK,
        Browser::EDG,
        '75',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3738.0 Safari/537.36 Edg/75.0.107.0',
    ],

    // IE Windows
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '11',
        'Mozilla/5.0 (compatible, MSIE 11, Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '10',
        'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '9',
        'Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '8',
        'Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; ' .
        '.NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '7.0b',
        'Mozilla/4.0(compatible; MSIE 7.0b; Windows NT 6.0)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '7.0b',
        'Mozilla/4.0 (compatible; MSIE 7.0b; Windows NT 5.1; Media Center PC 3.0; .NET CLR 1.0.3705; .NET CLR 1.1.4322; .NET CLR 2.0.50727; InfoPath.1)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '7',
        'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 5.2)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '6.1',
        'Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '6',
        'Mozilla/4.0 (compatible;MSIE 6.0;Windows 98;Q312461)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '7',
        'Mozilla/4.0 (compatible; MSIE 7.0; AOL 9.6; AOLBuild 4340.128; Windows NT 5.1; Trident/4.0; .NET CLR 1.1.4322; ' .
        '.NET CLR 2.0.50727; .NET CLR 3.0.04506.648; .NET CLR 3.5.21022)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '8',
        'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; ' .
        '.NET CLR 3.0.30729; .NET4.0C; Maxthon 2.0)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '7',
        'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; SlimBrowser)',
    ],

    // Windows Phone
    [
        Browser::DEVICE_WINDOWS_PHONE,
        true,
        Browser::ENGINE_TRIDENT,
        Browser::IE,
        '10',
        'Mozilla/5.0 (compatible; MSIE 10.0; Windows Phone 8.0; Trident/6.0; IEMobile/10.0; ARM; Touch; NOKIA; Lumia 920)',
    ],

    // Chrome Mac
    [
        Browser::DEVICE_MAC,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::CHROME,
        '41.0.2227.1',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
    ],

    // Chrome Windows
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::CHROME,
        '41.0.2228.0',
        'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
    ],

    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::CHROME,
        '15.0.864.0',
        'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.864.0 Safari/535.2',
    ],

    // Chrome Linux
    [
        Browser::DEVICE_LINUX,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::CHROME,
        '41.0.2227.0',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
    ],

    // Safari Windows
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '5.0.4',
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US) AppleWebKit/533.20.25 (KHTML, like Gecko) Version/5.0.4 Safari/533.20.27',
    ],
    [
        Browser::DEVICE_MAC,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '5.0.3',
        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_5; ar) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.3 Safari/533.19.4',
    ],

    // Safari Blackberry
    [
        Browser::DEVICE_BLACKBERRY,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '6.0.0.546',
        'Mozilla/5.0 (BlackBerry; U; BlackBerry 9700; pt) AppleWebKit/534.8+ (KHTML, like Gecko) Version/6.0.0.546 Mobile Safari/534.8+',
    ],
    [
        Browser::DEVICE_BLACKBERRY,
        true,
        Browser::ENGINE_WEBKIT,
        '',
        '',
        'BlackBerry9700/5.0.0.862 Profile/MIDP-2.1 Configuration/CLDC-1.1 VendorID/120',
    ],

    // Safari Android Tablet
    [
        Browser::ANDROID_TABLET,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '999.9',
        'Mozilla/5.0 (Linux; U; Android 2.3; en-us) AppleWebKit/999+ (KHTML, like Gecko) Safari/999.9',
    ],

    [
        Browser::ANDROID_TABLET,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4',
        'Mozilla/5.0 (Linux; U; Android 3.0; en-us; Xoom Build/HRI39) AppleWebKit/534.13 (KHTML, like Gecko) Version/4.0 Safari/534.13',
    ],
    [
        Browser::ANDROID_TABLET,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4',
        'Mozilla/5.0 (Linux; U; Android 2.3.4; en-us; Silk/1.1.0-84) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 ' .
        'Mobile Safari/533.1 Silk-Accelerated=false',
    ],
    [
        Browser::ANDROID_TABLET,
        true,
        Browser::ENGINE_GECKO,
        Browser::FIREFOX,
        '12',
        ' Mozilla/5.0 (Android; Tablet; rv:12.0) Gecko/12.0 Firefox/12.0',
    ],
    [
        Browser::ANDROID_TABLET,
        true,
        Browser::ENGINE_PRESTO,
        Browser::OPERA,
        '11.5',
        'Opera/9.80 (Android 3.2.1; Linux; Opera Tablet/ADR-1111101157; U; en) Presto/2.9.201 Version/11.50',
    ],

    // Safari Android
    [
        Browser::DEVICE_ANDROID,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4',
        'Mozilla/5.0 (Linux; U; Android 2.2.1; en-ca; LG-P505R Build/FRG83) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',
    ],

    // Safari iPad
    [
        Browser::DEVICE_IPAD,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4.0.4',
        'Mozilla/5.0(iPad; U; CPU iPhone OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 ' .
        'Mobile/7B314 Safari/531.21.10gin_lib.cc',
    ],
    [
        Browser::DEVICE_IPAD,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4.0.4',
        'Mozilla/5.0(iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 ' .
        'Mobile/7B314 Safari/531.21.10gin_lib.cc',
    ],

    // iPhone
    [
        Browser::DEVICE_IPHONE,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4.0.5',
        'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_1 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 ' .
        'Mobile/8B5097d Safari/6531.22.7',
    ],

    // iPod
    [
        Browser::DEVICE_IPOD,
        true,
        Browser::ENGINE_WEBKIT,
        Browser::SAFARI,
        '4.0.4',
        'Mozilla/5.0(iPod; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 ' .
        'Mobile/7B314 Safari/531.21.10gin_lib.cc',
    ],

    // Firefox
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_GECKO,
        Browser::FIREFOX,
        '3.6.9',
        'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.9) Gecko/20100824 Firefox/3.6.9 ( .NET CLR 3.5.30729; .NET CLR 4.0.20506)',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_GECKO,
        Browser::FIREFOX,
        '4.0b8pre',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:2.0b8pre) Gecko/20101213 Firefox/4.0b8pre',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_GECKO,
        Browser::FIREFOX,
        '5',
        'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20100101 Firefox/5.0',
    ],
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_GECKO,
        Browser::FIREFOX,
        '6',
        'Mozilla/5.0 (Windows NT 5.0; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0',
    ],
    [
        Browser::DEVICE_MAC,
        false,
        Browser::ENGINE_GECKO,
        '',
        '',
        'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en; rv:1.9.2.14pre) Gecko/20101212 Camino/2.1a1pre (like Firefox/3.6.14pre)',
    ],

    // KHTML
    [
        Browser::DEVICE_LINUX,
        false,
        Browser::ENGINE_KHTML,
        '',
        '',
        'Mozilla/5.0 (compatible; Konqueror/4.4; Linux 2.6.32-22-generic; X11; en_US) KHTML/4.4.3 (like Gecko) Kubuntu',
    ],

    // Vivaldi Windows
    [
        Browser::DEVICE_WINDOWS,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::VIVALDI,
        '1.0.118.19',
        'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/40.0.2214.115 Safari/537.36 Vivaldi/1.0.118.19',
    ],

    // Vivaldi Linux
    [
        Browser::DEVICE_LINUX,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::VIVALDI,
        '1.0.142.32',
        'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.105 Safari/537.36 Vivaldi/1.0.142.32',
    ],

    // Vivaldi Mac
    [
        Browser::DEVICE_MAC,
        false,
        Browser::ENGINE_WEBKIT,
        Browser::VIVALDI,
        '1.0.303.52',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.99 Safari/537.36 Vivaldi/1.0.303.52',
    ],

    // Amaya
    [
        '',
        false,
        Browser::AMAYA,
        '',
        '',
        'amaya/11.3.1 libwww/5.4.1',
    ],
];
