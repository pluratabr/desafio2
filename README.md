# Desafio 2

## Equipe 
Alecsander Sena, Gabriel Alves, Rafaela Bueno, Raul Carvalho, Ruben Cruz, Yuri Serka.

## Link da Solução 
https://hackathon.plurata.com.br/assets/pages/desafio2.html

## Requisitos para utilização do código

 - Rstudio: 1.2.5042
 - R version 4.0.0 (2020-04-24)

## Como executar o código
O código pode ser utilizado através da interface do Rstudio ou via RScript.


## Licença 
Os códigos foram gerados com linguagem de programação open source (R), e a ferramenta para os dashboards também é gratuita (Data studio).


*Desafio 2: inconsistência de dados nos sistemas dos Tribunais.*  

Olá, nós somos a Equipe Plurata, o Team 17 no Shawee.
 

Seguindo o propósito do desafio “Inconsistência de dados nos sistemas dos Tribunais”, a Equipe Plurata direcionou a solução para os possíveis problemas relacionados aos metadados, mais especificamente nas características Classe e Assunto dos processos, bem como para as necessidades apontadas nos relatos disponibilizados.  

Segundo nossos especialistas, a atuação e distribuição dos processos físicos eram feitas pelos setores de distribuição processual, que detinham considerável expertise nessa tarefa. Com a adoção do processo eletrônico, essa tarefa de autuar o processo passou a ser feita pelos advogados e procuradores. Os registros da classe e assunto estão entre os dados a serem preenchidos nos sistemas eletrônicos, mas nota-se que há uma quantidade considerável de registros equivocados de assuntos para determinadas classes, impedindo uma consistente análise estatística de dados e, consequentemente, uma boa organização do trabalho das serventias judiciais.

  

Como exemplo de tais equívocos, há o registro de um assunto tipicamente criminal (Tráfico de Drogas e Condutas Afins) em uma classe de processo de Execução Fiscal (Embargos Infringentes na Execução Fiscal).

  

Pensando nisso, a Plurata desenvolveu o "Buscador de Inconsistências" que suporta os advogados no momento do cadastro de seus processos no sistema PJe através de notificações de alerta quando não há correspondência entre a classe e o assunto do processo, por exemplo. Por trás dessa solução, algoritmos de aprendizagem supervisionada de Machine Learning processam esses dados em tempo real e informam a probabilidade de detecção de anomalias dos processos através de informações de tribunais, classe e frequência da correspondência classe-assunto.

  

Para realizar a leitura dos arquivos JSON disponibilizados, foi desenvolvida uma rotina de ETL usando a ferramenta Pentaho Data Integration (PDI). O tratamento dos dados foi realizado em três fases. Primeiramente, a conversão de formato dos arquivos JSON para CSV via Python para ganhar velocidade de leitura dos arquivos de entrada. Posteriormente, o processamento dos arquivos CSV através de ETL e armazenamento no banco de dados Postgres no modelo dimensional. Por fim, é realizado o cálculo das métricas para serem utilizados nas tabelas de processos e movimentações.

  

No que diz respeito à arquitetura, a figura esquemática demonstra as diferentes soluções utilizadas.

  

Do ponto de vista tecnológico, nossa solução é composta de:

-   Uma aplicação web escrita nas linguagens HTML 5 + Javascript.
    
-   Um modelo de regressão logística escrito na linguagem R.
    
-   Um modelo de clusterização escrito na linguagem R.
    
-   Um banco de dados Postgres.
    
-   Um servidor de notificações (PUSH) instantâneas.
    
-   Uma extensão para navegadores.
    
-   Uma API REST (Representational State Transfer) para interoperabilidade de recursos entre os componentes distribuídos