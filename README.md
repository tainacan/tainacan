# Repositorio de testes 

Repositorio pra testar uma estrutura possível para o novo repositorio do WP

## Descrição do que a gente quer

A gente quer um repositorio onde teremos o codigo fonte do plugin, além de outras ferramentas necessárias para o desenvolvimento, como uma suíte de testes e componentes necessários pra desenvolvimento dos componentes JS

Nesse repositorio, quando tivermos uma versão para publicar, rodaremos um script de build e ele gerará uma outra pasta, com o código do plugin todo pronto, sass e js compilado, etc.

## Descrição desse esqueleto

Nesse repositorio temos duas pastas:

* src/ - onde fica o código do plugins
* tests/ - onde ficam as ferramentas de testes

Na pasta `src` temos os arquivos normais de um plugin de WP. Essa será a pasta base do plugin e apenas o que estiver aí dentro é o que vai pra distribuição final do plugin. Já criei uns métodos quaisquer lá pra poder testar uma classe fictícia `TainacanCollections`.

Aí dentro podem haver arquivos sass e js que ainda serão compilados no build.

Na pasta `tests` temos a suíte de testes e as ferramentas para configurá-la. Já tem uns primeiros testes lá de exemplo que testa a classe `TainacanCollections`.

Na raíz temos alguns scripts importantes:

* build.sh - é o que faz o build (tá bem tosco ainda)
* compile-sass.sh - compila os arquivos scss do plugin
* run-tests.sh - monta o ambiente de testes e roda os testes 
* outros arquivos do phpunit que podemos testar pra ver se precisam ficar assim na raíz mesmo.


## Montando ambiente e rodando testes

Dá pra perceber que, como esse repositorio tem outras pastas além do plugin, não dá pra gente fazer o clone dela dentro da pasta de plugin do WP e sair usando.

Temos que pensar como fazer isso, talvez um esquema que fique rodando o build e jogando o plugin pra dentro da nossa instalação de testes do WP. É assim que o `build.sh` ta fazendo hoje.

Pra montar o ambiente de testes, foram seguidas essas instruções: https://make.wordpress.org/cli/handbook/plugin-unit-tests/

Basicamente você vai precisar instalar o `phpunit` e aí rodar o comando `tests/bin/install-wp-tests.sh` **passando os parâmetros certos**. Por exemplo:

```
tests/bin/install-wp-tests.sh wordpress_test root '' localhost latest
```
Os parâmetros são:

* nome do DB
* usuario do MySQL
* senha do MySQL
* host do MySQL
* versão do WP

Esse comando só precisa rodar uma vez. pra instalar o ambiente.

Depois disso, pra rodar os testes, é só rodar o comando `phpunit`.

**Extras**

Vale a pena ver essa série de posts: https://pippinsplugins.com/unit-tests-wordpress-plugins-introduction/

E pra usuários Windows, tem umas instruções lá de como fazer rodar: https://make.wordpress.org/cli/handbook/plugin-unit-tests/#integrating-wp-unit-testing-in-windows
