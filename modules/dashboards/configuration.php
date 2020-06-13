<?php

$section = $this->menuSection(N_('Dashboards'), [
    'icon'  => 'home',
    'url'   => \ipl\Web\Url::fromPath('dashboards'),
    'priority'  => 50
]);

$section->add(N_('X509'), [
    'url'   => \ipl\Web\Url::fromPath('dashboards')->addParams(['home' => 'X509'])
]);

$section->add(N_('Director'), [
    'url'   => \ipl\Web\Url::fromPath('dashboards')->addParams(['home' => 'Director'])
]);
