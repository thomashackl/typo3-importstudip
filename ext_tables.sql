CREATE TABLE IF NOT EXISTS `tx_importstudip_config` (
    `route` VARCHAR(255) NOT NULL,
    `data` TEXT NOT NULL,
    `mkdate` INT NOT NULL,
    `chdate` INT NOT NULL,
    PRIMARY KEY (`route`)
);

CREATE TABLE IF NOT EXISTS `tx_importstudip_externalpages` (
    `url` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `mkdate` INT NOT NULL,
    `chdate` INT NOT NULL,
    PRIMARY KEY (`url`)
);
