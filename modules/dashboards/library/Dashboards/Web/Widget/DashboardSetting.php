<?php

namespace Icinga\Module\Dashboards\Web\Widget;

use Icinga\Authentication\Auth;
use Icinga\Module\Dashboards\Common\Database;
use InvalidArgumentException;
use ipl\Html\BaseHtmlElement;
use ipl\Html\Html;
use ipl\Sql\Select;
use function ipl\Stdlib\get_php_type;

class DashboardSetting extends BaseHtmlElement
{
    use Database;

    /** @var iterable $dashboards public dashboards */
    protected $dashboards;

    protected $defaultAttributes = ['class' => 'content setting'];

    protected $tag = 'div';

    /**
     * Create a new dashboards and dashlets setting
     *
     * @param iterable $dashboards All public dashboards from the database
     *
     * @throws InvalidArgumentException If $dashboards|$userDashboards are not iterable
     */
    public function __construct($dashboards)
    {
        if (!is_iterable($dashboards)) {
            throw new InvalidArgumentException(sprintf(
                '%s expects parameter 1 to be iterable, got %s instead',
                __METHOD__,
                get_php_type($dashboards)
            ));
        }

        $this->dashboards = $dashboards;
    }

    /**
     * @inheritDoc
     *
     * ipl/Html lacks a call to {@link BaseHtmlElement::ensureAssembled()} here. This override is subject to remove once
     * ipl/Html incorporates this fix.
     */
    public function isEmpty()
    {
        $this->ensureAssembled();

        return parent::isEmpty();
    }

    /**
     * Display the dashboards and dashlets setting
     *
     * @return \ipl\Html\HtmlElement
     */
    public function settingsAction()
    {
        $this->add(Html::tag('h1', null, t('Dashboard Settings')));

        $table = Html::tag('table', [
            'class' => 'avp action',
            'data-base-target' => '_next'
        ]);

        $table->add(Html::tag('thead', null, Html::tag('tr', null, [
            Html::tag('th', [
                'style' => 'width: 18em;'
            ], Html::tag('strong', null, t('Dashlet Name'))),
            Html::tag('th', null, Html::tag('strong', null, 'Url')),
            Html::tag('th', [
                'style' => 'width: 1.48em;'
            ])])));

        $tbody = Html::tag('tbody');

        foreach ($this->dashboards as $dashboard) {
            $tbody->add(new DashboardDetails($dashboard));

            $select = (new Select())
                ->from('dashlet')
                ->columns('*')
                ->where([
                    'dashboard_id = ?' => $dashboard->id,
                    'type = "system" OR owner = ?' => Auth::getInstance()->getUser()->getUsername()
                ])
                ->orderBy('dashlet.id');

            $dashlets = $this->getDb()->select($select);

            foreach ($dashlets as $dashlet) {
                $tbody->add(new DashletDetails($dashlet, $dashboard));
            }
        }

        $table->add($tbody);

        return $table;
    }

    protected function assemble()
    {
        $this->add($this->settingsAction());
    }
}
