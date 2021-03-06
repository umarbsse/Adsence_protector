<?php
    function getOS(){
        $platforms = array(
            'windows nt 10.0'   => 'Windows 10',
            'windows nt 6.3'    => 'Windows 8.1',
            'windows nt 6.2'    => 'Windows 8',
            'windows nt 6.1'    => 'Windows 7',
            'windows nt 6.0'    => 'Windows Vista',
            'windows nt 5.2'    => 'Windows 2003',
            'windows nt 5.1'    => 'Windows XP',
            'windows nt 5.0'    => 'Windows 2000',
            'windows nt 4.0'    => 'Windows NT 4.0',
            'winnt4.0'          => 'Windows NT 4.0',
            'winnt 4.0'         => 'Windows NT',
            'winnt'             => 'Windows NT',
            'windows 98'        => 'Windows 98',
            'win98'             => 'Windows 98',
            'windows 95'        => 'Windows 95',
            'win95'             => 'Windows 95',
            'windows phone'         => 'Windows Phone',
            'windows'           => 'Unknown Windows OS',
            'android'           => 'Android',
            'blackberry'        => 'BlackBerry',
            'iphone'            => 'iOS',
            'ipad'              => 'iOS',
            'ipod'              => 'iOS',
            'os x'              => 'Mac OS X',
            'ppc mac'           => 'Power PC Mac',
            'freebsd'           => 'FreeBSD',
            'ppc'               => 'Macintosh',
            'linux'             => 'Linux',
            'debian'            => 'Debian',
            'sunos'             => 'Sun Solaris',
            'beos'              => 'BeOS',
            'apachebench'       => 'ApacheBench',
            'aix'               => 'AIX',
            'irix'              => 'Irix',
            'osf'               => 'DEC OSF',
            'hp-ux'             => 'HP-UX',
            'netbsd'            => 'NetBSD',
            'bsdi'              => 'BSDi',
            'openbsd'           => 'OpenBSD',
            'gnu'               => 'GNU/Linux',
            'unix'              => 'Unknown Unix OS',
            'symbian'           => 'Symbian OS'
        );
        foreach ($platforms as $key => $val)
        {
            if (preg_match('|'.preg_quote($key).'|i', $_SERVER['HTTP_USER_AGENT'])){
                return $val;
            }
        }
        return "Unknown OS Platform";
    }
?>