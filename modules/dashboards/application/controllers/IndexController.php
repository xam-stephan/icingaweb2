<?php

namespace Icinga\Module\Dashboards\Controllers;

use Icinga\Authentication\Auth;
use Icinga\Exception\ConfigurationError;
use Icinga\Exception\NotFoundError;
use Icinga\Module\Dashboards\Common\Database;
use Icinga\Module\Dashboards\Form\DashboardsHomeForm;
use Icinga\Module\Dashboards\Web\Controller;
use Icinga\Module\Dashboards\Web\Widget\Tabextension\DashboardAction;
use Icinga\Module\Dashboards\Web\Widget\DashboardWidget;
use Icinga\Web\Notification;
use Icinga\Web\Url;
use ipl\Sql\Select;

class IndexController extends Controller
{
    use Database;

    public function indexAction()
    {
        if (! array_key_exists('home', $this->getAllParams())) {
            $this->setTitle($this->translate('Available Dashlets'));
            $select = (new Select())
                ->columns('dashboard.*')
                ->from('dashboard')
                ->join('dashboard_home', 'dashboard.home_id = dashboard_home.id')
                ->where([
                    'dashboard.owner = ?' => Auth::getInstance()->getUser()->getUsername()
                ]);

            $dashboards = $this->getDb()->select($select);

            $content = new DashboardsHomeForm($dashboards);

            $this->addContent($content);
        } else {
            try {
                $db = $this->getDb();
            } catch (ConfigurationError $_) {
                $this->render('missing database \'dashboard\'', null, true);
                return;
            }

            try {
                $this->createTabsAndAutoActivateDashboard();
            } catch (NotFoundError $_) {
                Notification::error('No dashboards found');
            }

            $select = (new Select())
                ->columns('dashlet.*')
                ->from('dashlet')
                ->join('dashboard d', 'dashlet.dashboard_id = d.id')
                ->join('dashboard_home dh', 'd.home_id = dh.id')
                ->join('dashlet_order', 'dashlet.id = dashlet_order.dashlet_id')
                ->where([
                    'dashlet.dashboard_id = ?' => $this->tabs->getActiveName(),
                    'dh.name = ?'   => $this->params->get('home')
                ])
                ->orderBy('dashlet_order.`order`', SORT_DESC);

            $dashlets = $db->select($select);

            $this->content = new DashboardWidget($dashlets);
        }
    }

    /**
     * create Tabs and
     * activate the first dashboard from the database when the url $params doesn't given
     *
     * @return int
     *
     * @throws \Icinga\Exception\NotFoundError      If the database doesn't have a dashboard
     */
    protected function createTabsAndAutoActivateDashboard()
    {
        $tabs = $this->getTabs();

        $select = (new Select())
            ->columns('dashboard.*')
            ->from('dashboard')
            ->join('dashboard_home', 'dashboard.home_id = dashboard_home.id')
            ->where([
                'dashboard.owner = ?' => Auth::getInstance()->getUser()->getUsername(),
                'dashboard_home.name = ?' => $this->params->get('home')
            ]);

        $query = (new Select())
            ->columns('dashboard.*')
            ->from('dashboard')
            ->join('dashboard_home', 'dashboard.home_id = dashboard_home.id')
            ->join('dashboard_user', 'dashboard_user.dashboard_id = dashboard.id')
            ->where([
                'dashboard_user.user = ?' => Auth::getInstance()->getUser()->getUsername()
            ]);

        $query->unionAll($select)
            ->joinRight(
                'dashboard_home_order dho',
                ['dho.home = dashboard_home.name AND dho.user = ?' => Auth::getInstance()->getUser()->getUsername()]
            )
            ->groupBy('dashboard_home.name, dho.`order`')
            ->orderBy('dho.`order`, dashboard_home.name');

        $dashboards = $this->getDb()->select($select);

        foreach ($dashboards as $dashboard) {
            $tabs->add($dashboard->id, [
                'label' => $dashboard->name,
                'url' => Url::fromPath('dashboards', [
                    'home'      => $this->params->get('home'),
                    'dashboard' => $dashboard->id
                ])
            ])->extend(new DashboardAction())->disableLegacyExtensions();

            $ids[] = $dashboard->id;
        }

        $id = $this->params->get('dashboard') ?: array_shift($ids);

        $tabs->activate($id);

        return $id;
    }
}
