CREATE TABLE `tx_importstudip_config` (
    `route` VARCHAR(255) DEFAULT '' NOT NULL,
    `data` MEDIUMTEXT NOT NULL,
    `mkdate` INT DEFAULT 0 NOT NULL,
    `chdate` INT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`route`)
);

CREATE TABLE `tx_importstudip_externalpages` (
    `url` VARCHAR(255) DEFAULT '' NOT NULL,
    `content` MEDIUMTEXT NOT NULL,
    `mkdate` INT DEFAULT 0 NOT NULL,
    `chdate` INT DEFAULT 0 NOT NULL,
    PRIMARY KEY (`url`)
);
