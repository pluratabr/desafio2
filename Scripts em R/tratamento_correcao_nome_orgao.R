library(DBI)
library(RPostgres)
library(dplyr)
library(stringi)
library(stringr)
db <- 'db_cnj'  #provide the name of your db

host_db <- '157.230.184.48' #i.e. # i.e. 'ec2-54-83-201-96.compute-1.amazonaws.com'  

db_port <- '5432'  # or any other port specified by the DBA

db_user <- 'postgres'  

db_password <- '123456'

con <- dbConnect(RPostgres::Postgres(), dbname = db, host=host_db, port=db_port, user=db_user, password=db_password)

#correcao por tribunal esepcifico
processos<- dbGetQuery(con, "SELECT t.tribunal,
                            t.numeroprocesso,
                            t.orgaojulgadorcodigoorgao,
                            t.orgaojulgadornomeorgao,
                            t.orgaojulgadormunicipioibge,
                            t.orgaojulgadormunicipio,
                            t.orgaojulgadoruf
                            FROM public.processos_correcao t where tribunal = 'TJAL'")

serventias_cidades <- dbGetQuery(con,"SELECT * FROM public.serventias_referencia")


names(serventias_cidades)<-c("codigoorgao","nomeorgao",
                             "codigoibge",  "uf", "municipio")
serventias_cidades$codigoibge<-as.character(serventias_cidades$codigoibge)
serventias_cidades$codigoorgao<-as.character(serventias_cidades$codigoorgao)

#funcao que normaliza as strings do nome do orgao e compara similariade.
#remove acentos, pontuacao, tokeniza a string, ordena alfabeticamente,
#concatena todas as palavras e por fim faz a comparacao retornando a similaridade
similaridade_nome<-function(x,y){
  x<-stri_trans_tolower(stri_trans_general(x,"Latin-ASCII"))
  x<-lapply(str_split(x, pattern = " - | / | "),str_sort)%>%
    lapply(paste, collapse="")%>%
    unlist()
  
  y<-stri_trans_tolower(stri_trans_general(y,"Latin-ASCII"))
  y<-lapply(str_split(y, pattern = " - | / | "),str_sort)%>%
    lapply(paste, collapse="")%>%
    unlist()
  if(!is.null(x)&&!is.null(y)){
    stringdist::stringsim(x,y)
  }else{
    0.0
  }
}

#funcao que compara o nome do orgao com o nome registrado na tabela do MPM
#retorna uma lista contendo a similaridade entre os nomes o codigo do orgao
#e o nome aproximado
corrige_cod_orgao<-function(cod_ibge,nome_orgao,cod_orgao){
  orgaos_filtrados<-filter(serventias_referencia, codigoibge == cod_ibge)%>%
    select(codigoorgao,nomeorgao)
  
  similaridade<-similaridade_nome(nome_orgao,orgaos_filtrados$nomeorgao)
  cat("similaridade:",max(similaridade),"\n")
  codigo<-orgaos_filtrados$codigoorgao[which.max(similaridade)]
  org_aprox<-orgaos_filtrados$nomeorgao[which.max(similaridade)]
  
  list(data.frame(correcao=codigo,similaridade=max(similaridade),nome_proximo=org_aprox))
  
}

processos_erro<-processos%>%
  #mutate(orgaojulgadorcodigoorgao.y=orgaojulgadorcodigoorgao)%>%
  left_join(serventias_cidades, by=c("orgaojulgadorcodigoorgao"="codigoorgao"))%>%
  mutate(similaridade=similaridade_nome(orgaojulgadornomeorgao,nomeorgao))%>%
  filter(orgaojulgadormunicipioibge!=codigoibge)%>%
  filter(similaridade<0.50)%>%
  select(orgaojulgadormunicipioibge,orgaojulgadornomeorgao,codigoibge,
         nomeorgao,orgaojulgadorcodigoorgao,numeroprocesso)
  

#processos_erro<-processos_correcao%>%
#  filter(orgaojulgadormunicipioibge!=codigoibge)%>%
#  filter(similaridade<0.50)%>%
#  select(orgaojulgadormunicipioibge.x,orgaojulgadornomeorgao.x,orgaojulgadormunicipioibge.y,
#         orgaojulgadornomeorgao.y,orgaojulgadorcodigoorgao,numeroprocesso)



bloco<-processos_erro%>%
  select(-numeroprocesso)%>%
  group_by(orgaojulgadormunicipioibge,orgaojulgadornomeorgao,codigoibge,
           nomeorgao,orgaojulgadorcodigoorgao)%>%unique()

cod_corrigido<-mapply(corrige_cod_orgao, bloco$orgaojulgadormunicipioibge,
                      bloco$orgaojulgadornomeorgao,bloco$orgaojulgadorcodigoorgao)


cod_df<-do.call("bind_rows",cod_corrigido)

bloco<-bind_cols(bloco,cod_df)

correcoes_validas<-bloco%>%
  filter(similaridade >=1)


correcoes_aproximadas<-bloco%>%
  filter(similaridade <1)


processos_selecao<-correcoes_validas%>%
  left_join(processos_erro, by =c("orgaojulgadornomeorgao"="orgaojulgadornomeorgao","nomeorgao"="nomeorgao"))%>%
  ungroup()%>%
  select(correcao,numeroprocesso)%>%
  mutate(sql_query=paste0("('",correcao,"','",numeroprocesso,"')"))

db_query=paste0("update public.processos_correcao as m set orgaojulgadorcodigoorgao = c.orgaojulgadorcodigoorgao from (values",paste(processos_selecao$sql_query, collapse = ","),") as c(orgaojulgadorcodigoorgao, numeroprocesso) where c.numeroprocesso = m.numeroprocesso")

update <- dbSendQuery(con, db_query)
dbClearResult(update)




#correção de toda base
inconsistencia_query<-"
SELECT s.*, p.*
FROM public.serventias_referencia s
inner join
(SELECT distinct tribunal,
                 orgaojulgadorcodigoorgao,
                 orgaojulgadornomeorgao,
                 orgaojulgadormunicipioibge,
                 orgaojulgadormunicipio, orgaojulgadoruf
                 FROM public.processos where
                 orgaojulgadormunicipio notnull
                 and orgaojulgadoruf notnull ) p on (s.codigoorgao = p.orgaojulgadorcodigoorgao and s.codigoibge != p.orgaojulgadormunicipioibge);
"

inconsistencia_codigo<- dbGetQuery(con,inconsistencia_query)




serventias_referencia<- dbGetQuery(con,"SELECT * FROM public.serventias_referencia")



cod_corrigido<-mapply(corrige_cod_orgao, inconsistencia_codigo$orgaojulgadormunicipioibge,
                      inconsistencia_codigo$orgaojulgadornomeorgao,inconsistencia_codigo$orgaojulgadorcodigoorgao)


cod_df<-do.call("bind_rows",cod_corrigido)

inconsistencia_codigo<-bind_cols(inconsistencia_codigo,cod_df)

correcoes_validas<-inconsistencia_codigo%>%
  filter(similaridade >=1)


correcoes_aproximadas<-inconsistencia_codigo%>%
  filter(similaridade <1)

correcao<-correcoes_validas%>%
  select(orgaojulgadorcodigoorgao, orgaojulgadornomeorgao, orgaojulgadormunicipioibge, correcao)%>%
  mutate(sql_query=paste0("('",orgaojulgadorcodigoorgao,"','",orgaojulgadornomeorgao,"',",orgaojulgadormunicipioibge,",'",correcao,"')"))


db_query<-paste0("update public.processos_correcao as m set orgaojulgadorcodigoorgao = c.correcao from (values",paste(correcao$sql_query, collapse = ","),") as c(orgaojulgadorcodigoorgao, orgaojulgadornomeorgao, orgaojulgadormunicipioibge,correcao) where c.orgaojulgadorcodigoorgao = m.orgaojulgadorcodigoorgao and c.orgaojulgadornomeorgao = m.orgaojulgadornomeorgao and c.orgaojulgadormunicipioibge = m.orgaojulgadormunicipioibge")

update <- dbSendQuery(con, db_query)
dbClearResult(update)



