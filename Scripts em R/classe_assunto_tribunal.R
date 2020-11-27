library(dplyr)
library(tidyverse)
library(jsonlite)
library(readr)
library(lubridate)
library(data.table)

#setwd("hackathon/base/")
setwd("/Users/micaelfilipe/Documents/Plurata/gitlab/hackathon/base")
sgt_assuntos <- read_delim("sgt_assuntos.csv", 
                           ";", escape_double = FALSE, col_types = cols(cod_filhos = col_character()), 
                           trim_ws = TRUE)

sgt_classes <- read_delim("sgt_classes.csv", 
                          ";", escape_double = FALSE, col_types = cols(cod_filhos = col_character(), codigo = col_character()), 
                          trim_ws = TRUE)





gera_percentual<-function(temp_tribunal,i){
  `%notin%`<-Negate(`%in%`)
  nome_saida<-str_split(i,"/")[[1]][3]
  cat(nome_saida, "\n")
  nome_saida<-paste0("saida_perc/",gsub(".json", ".csv", nome_saida))
  tribunal1_1<-temp_tribunal
  tribunal1_1<- tribunal1_1%>%
    left_join(select(sgt_classes, codigo, descricao), by = c("dadosBasicos.classeProcessual"="codigo"))%>%
    mutate(descricao_classe = descricao)
  
  tribunal1_1<-group_by(tribunal1_1, siglaTribunal)
  
  tribunais<-group_split(tribunal1_1)
  
  gera_df<- function(tribunal, sgt_assuntos){
    tribunal_assuntos<- tribunal %>% select(dadosBasicos.numero, dadosBasicos.assunto,
                                            dadosBasicos.classeProcessual,
                                            descricao_classe,
                                            siglaTribunal,dadosBasicos.orgaoJulgador.codigoOrgao)%>%
      unnest(dadosBasicos.assunto)
  }
  
  dfs_tribunal_list<-lapply(tribunais, gera_df, sgt_assuntos)
  
  df_tribunal<- do.call(bind_rows, dfs_tribunal_list)
  
  if (!any(grepl("codigoNacional|assuntoLocal.codigoPaiNacional",names(df_tribunal)))){
    df_tribunal$codigoNacional<-NA
  }
  
  tribunal_assuntos<- df_tribunal%>% 
    mutate(cod_assunto = ifelse("codigoNacional"%in% names(.),codigoNacional,assuntoLocal.codigoPaiNacional ))%>%
    #select(-descricao)%>%
    left_join(select(sgt_assuntos, codigo), by = c("cod_assunto"="codigo"))#%>%
    #mutate(descricao_assunto = descricao)%>%
    #select(-descricao)%>%
    #select(-assuntoLocal.codigoAssunto, -assuntoLocal.codigoPaiNacional, -codigoNacional)
  
  
  assuntos_classe<- tribunal_assuntos%>%
    group_by(dadosBasicos.numero)%>%
    mutate(cod_assunto_classe = paste(cod_assunto, dadosBasicos.classeProcessual))
  
  
  assuntos_classe_percentual<- assuntos_classe%>%
    group_by(cod_assunto_classe,siglaTribunal,dadosBasicos.orgaoJulgador.codigoOrgao)%>%
    #filter(cod_mov != "85_85")%>%
    summarise(ocorrencia= n())%>%
    ungroup()%>%
    mutate(percentual = paste(as.character(round(ocorrencia/sum(ocorrencia)*100, digits = 3)), "%"))
  
  write.csv(assuntos_classe_percentual, nome_saida)
}


jf<-list.files("justica_federal", full.names = T)
for (tr in jf ){
  tribunal<-list.files(tr, full.names = T)
  for(i in tribunal){
    temp_tribunal <- fromJSON(i, flatten = TRUE)%>%
      mutate_if(is.numeric,funs(as.character(.)))
    gera_percentual(temp_tribunal,i)
    #tribunal1_1 <- bind_rows(temp_tribunal, tribunal1_1)
    cat(i, "Ok \n")
  }
  
}

#save(tribunal1_1,df_tribunal,tribunal_assuntos, assuntos_classe, assuntos_classe_percentual, file="tj_1_todos.rda")
tribunal1_1<-data.frame()
jus<-list.files("saida_perc", full.names = T)
for(i in jus){
  temp_tribunal<-read_csv(i)
  temp_tribunal$dadosBasicos.orgaoJulgador.codigoOrgao<-as.character(temp_tribunal$dadosBasicos.orgaoJulgador.codigoOrgao)
  tribunal1_1 <- bind_rows(temp_tribunal, tribunal1_1)
  cat(i, "Ok \n")
}
write_csv2(tribunal1_1, "trf_codigo_classe_assunto.csv")
