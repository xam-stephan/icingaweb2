<?php

namespace Icinga\Module\Dashboards\Form;

use Icinga\Module\Dashboards\Common\Database;
use Icinga\Web\Url;
use InvalidArgumentException;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Sql\Select;
use function ipl\Stdlib\get_php_type;

class DashboardsHomeForm extends BaseHtmlElement
{
    use Database;

    protected $dashboards;

    protected $defaultAttributes = [
        'class'     => 'content home',
    ];

    protected $tag = 'div';

    public function __construct($dashboards)
    {
        if (! is_iterable($dashboards)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects parameter 1 to be iterable, got %s instead',
                __METHOD__,
                get_php_type($dashboards)
            ));
        }
        $this->dashboards = $dashboards;
    }

    protected function homeAction()
    {
        $table = Html::tag('table', [
            'class' => 'common-table',
        ]);

        $table->add(Html::tag('thead', null, Html::tag('tr', null, [
            Html::tag('th', [
                'style' => 'width: 18em;'
            ], Html::tag('strong', [
                'style' => 'font-weight: bolder; color: #0095bf; font-size: 15px;'
            ], t('Module'))),
            Html::tag('th', [
                'style' => 'font-weight: bolder; color: #0095bf; font-size: 15px;'
            ], Html::tag('strong', null, 'Dashboards')),
            Html::tag('th', [
                'style' => 'width: 1.48em;'
            ])])));

        $tbody = Html::tag('tbody');

        foreach ($this->dashboards as $dashboard) {
            $select = (new Select())
                ->columns('*')
                ->from('dashboard_home')
                ->where(['dashboard_home.id = ?' => $dashboard->home_id]);

            $home = $this->getDb()->select($select)->fetchObject();

            $trow = Html::tag('tr', [
                'href' => Url::fromPath('dashboards')->addParams(['home' => $home->name])
            ]);
            $trow->add([Html::tag('td', [
                'style' => 'font-weight: bolder; background: #f1f1f1;'
            ], $home->name), Html::tag('td', [
                'style' => 'table-layout: fixed; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'
            ], $dashboard->name),
                Html::tag('td', [
                    Html::tag('a', [
                        'href'  => 'dashboards/'
                    ], Html::tag('i', [
                        'class' => 'icon-pin'
                    ]))
                ])
            ]);

            $tbody->add($trow);
        }

        $table->add($tbody);

        return $table;
    }

    protected function assemble()
    {
        $this->add($this->homeAction());
    }
}
