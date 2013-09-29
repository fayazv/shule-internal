CREATE USER 'admin'@'localhost' IDENTIFIED BY 'menejawadata';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
CREATE USER 'contentreader'@'localhost' IDENTIFIED BY 'wizardpeople';
GRANT SELECT ON shuledirect.* TO 'reader'@'localhost';
