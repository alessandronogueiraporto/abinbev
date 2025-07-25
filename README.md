AbInBev Drupal Application
Este repositório contém a base do projeto Drupal 11 para o cliente AbInBev, utilizando o ambiente de desenvolvimento local com Lando.

Requisitos: Docker e Lando instalados.

Para iniciar, clone o repositório com git clone https://github.com/alessandronogueiraporto/abinbev.git e entre na pasta com cd abinbev. Em seguida, execute lando start para subir o ambiente.

Acesse o site local pelo endereço https://abinbev.lndo.site.

O PHPMyAdmin está disponível para facilitar o gerenciamento do banco de dados. O Lando atribui uma porta dinâmica para o PHPMyAdmin, verifique qual porta está ativa usando lando info. Um exemplo de URL é http://localhost:58152.

Comandos úteis incluem:
lando drush status para ver o status do Drupal,
lando drush cr para limpar caches,
lando drush uli para gerar um link de login único,
lando ssh para acessar o shell do appserver.