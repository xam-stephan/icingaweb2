DROP TABLE IF EXISTS  dashlet;
DROP TABLE IF EXISTS dashboard_user;
DROP TABLE IF EXISTS dashboard_home_order;
DROP TABLE IF EXISTS dashboard_order;
DROP TABLE IF EXISTS dashlet_order;
DROP TABLE IF EXISTS dashboard_share;
DROP TABLE IF EXISTS dashboard;
DROP TABLE IF EXISTS dashboard_home;

CREATE TABLE dashboard_home (
    id int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    owner varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard (
    id int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
    home_id int(10) unsigned NOT NULL COMMENT 'Dashboards to be shared',
    name varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    owner varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    shared enum('enforced', 'n', 'y') NOT NULL,
    CONSTRAINT fk_dashboard_dashboard_home FOREIGN KEY (home_id) REFERENCES dashboard_home (id) ON DELETE CASCADE ON UPDATE CASCADE
) Engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashlet (
    id int(10) unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
    dashboard_id int(10) unsigned NOT NULL,
    name varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    url varchar(2048) NOT NULL,
    owner varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    shared enum('enforced', 'n', 'y'),
    CONSTRAINT fk_dashlet_dashboard FOREIGN KEY (dashboard_id) REFERENCES dashboard (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard_user (
    dashboard_id int(10) unsigned COMMENT 'Dashboards enforced for certain users' NOT NULL,
    user varchar(254) NOT NULL COLLATE utf8mb4_unicode_ci,
    CONSTRAINT fk_user_dashboard_dashboard FOREIGN KEY (dashboard_id) REFERENCES dashboard (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard_home_order (
    home varchar(64) NOT NULL COMMENT 'Order of dashboard homes per user' COLLATE utf8mb4_unicode_ci,
    user varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    `order` tinyint unsigned NOT NULL
) Engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard_order (
    home varchar(64) NOT NULL COMMENT 'Order of dashboards per home per user' COLLATE utf8mb4_unicode_ci,
    dashboard varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    user varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    `order` tinyint unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE Table dashlet_order (
    dashlet VARCHAR(64) NOT NULL COMMENT 'Order of dashlets per home & per user' COLLATE utf8mb4_unicode_ci,
    user varchar(254) DEFAULT NULL COLLATE utf8mb4_unicode_ci,
    `order` tinyint unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE dashboard_share(
    dashboard_id int(10) unsigned NOT NULL COMMENT 'Shares taken per user' COLLATE utf8mb4_unicode_ci,
    user varchar(254) NOT NULL COLLATE utf8mb4_unicode_ci,
    home varchar(64) NOT NULL COLLATE utf8mb4_unicode_ci,
    CONSTRAINT fk_share_dashboard_dashboard FOREIGN KEY (dashboard_id) REFERENCES dashboard (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
