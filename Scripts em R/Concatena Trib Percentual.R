library(tidyverse)

setwd("~/Documents/CNJ Hackathon/base/Perc")

ass <- sgt_assuntos[,c(1,2)]
cla <- sgt_classes[,c(1,2)]
cla$codigo <- as.numeric(cla$codigo)


#TRE
tre <- read.csv("tre_codigo_classe_assunto.csv", header = TRUE, sep = ";")
tre <- tre %>% group_by(cod_assunto_classe) %>% 
  mutate(ocorrencia = sum(ocorrencia))
tre <- tre[!duplicated(tre$cod_assunto_classe),]
tre$percentual <-  tre$ocorrencia / 215555 * 100 #(tre %>% summarise(sum(ocorrencia)))
tre <- tre %>% mutate(cod = cod_assunto_classe) %>% 
  separate(cod, c("cod_assunto", "cod_classe"),sep = " ", convert = TRUE)
tre$tribunal <- "tre"; tre$justica <- "eleitoral"
tre <- tre[,c("tribunal", "justica","cod_assunto_classe",
              "ocorrencia","percentual")]

#TRT
trt <- read.csv("trt_codigo_classe_assunto.csv", header = TRUE, sep = ";")
trt <- trt %>% group_by(cod_assunto_classe) %>% 
  mutate(ocorrencia = sum(ocorrencia))
trt <- trt[!duplicated(trt$cod_assunto_classe),]
trt$percentual <-  trt$ocorrencia / 4346525 * 100 #(trt %>% summarise(sum(ocorrencia)))
trt <- trt %>% mutate(cod = cod_assunto_classe) %>% 
  separate(cod, c("cod_assunto", "cod_classe"),sep = " ", convert = TRUE)
trt$tribunal <- "trt"; trt$justica <- "trabalho"
trt <- trt[,c("tribunal", "justica","cod_assunto_classe",
              "ocorrencia","percentual")]

#TJE
tj <- read.csv("tj_codigo_classe_assunto.csv", header = TRUE, sep = ";")
tj <- tj %>% group_by(cod_assunto_classe) %>% 
  mutate(ocorrencia = sum(ocorrencia))
tj <- tj[!duplicated(tj$cod_assunto_classe),]
tj$percentual <-  tj$ocorrencia / 2170577 * 100#(tje %>% summarise(sum(ocorrencia)))
tj <- tj %>% mutate(cod = cod_assunto_classe) %>% 
  separate(cod, c("cod_assunto", "cod_classe"),sep = " ", convert = TRUE)
tj$tribunal <- "tj"; tj$justica <- "estadual"
tj <- tj[,c("tribunal", "justica","cod_assunto_classe",
            "ocorrencia","percentual")]

#TRF
trf <- read.csv("trf_codigo_classe_assunto.csv", header = TRUE, sep = ";")
trf <- trf %>% group_by(cod_assunto_classe) %>% 
  mutate(ocorrencia = sum(ocorrencia))
trf <- trf[!duplicated(trf$cod_assunto_classe),]
trf$percentual <-  trf$ocorrencia / 353348 * 100 #(trf %>% summarise(sum(ocorrencia)))
trf <- trf %>% mutate(cod = cod_assunto_classe) %>% 
  separate(cod, c("cod_assunto", "cod_classe"),sep = " ", convert = TRUE)
trf$tribunal <- "trf"; trf$justica <- "federal"
trf <- trf[,c("tribunal", "justica","cod_assunto_classe",
              "ocorrencia","percentual")]

#TM
tm <- read.csv("tm_codigo_classe_assunto.csv", header = TRUE, sep = ";")
tm <- tm %>% group_by(cod_assunto_classe) %>% 
  mutate(ocorrencia = sum(ocorrencia))
tm <- tm[!duplicated(tm$cod_assunto_classe),]
tm$percentual <-  tm$ocorrencia / 16882 * 100 #(tm %>% summarise(sum(ocorrencia)))
tm <- tm %>% mutate(cod = cod_assunto_classe) %>% 
  separate(cod, c("cod_assunto", "cod_classe"),sep = " ", convert = TRUE)
tm$tribunal <- "tm"; tm$justica <- "militar"
tm <- tm[,c("tribunal", "justica","cod_assunto_classe",
            "ocorrencia","percentual")]

ass_class <- trf %>% rbind(tre) %>% rbind(tj) %>% rbind(trt) %>% rbind(tm)
ass_class$ocorrencia <- as.numeric(ass_class$ocorrencia)
#cod_ass_perc$percentual <- as.numeric(sub(" %", "", cod_ass_perc$percentual))

ass_class <- ass_class %>% group_by(tribunal,justica,cod_assunto_classe) %>% 
  summarise(ocorrencia = sum(ocorrencia), 
            percentual = sum(percentual))
ass_class$percentual <- format(ass_class$percentual, scientific = FALSE) %>% 
  as.numeric()

ass_class <- ass_class %>% group_by(tribunal) %>% 
  arrange(tribunal, ocorrencia) %>%
  mutate(quartile = cumsum(ocorrencia / sum(ocorrencia)))

df <- merge(ass_new[,c(1,5)],clas_new[, c(1,6)])
df <- df %>% mutate(cod_assunto_classe_pai = paste(ass_principal, cl_princial))
df <- df %>% left_join(valid, by = "cod_assunto_classe_pai")
df <- df %>% mutate(cod_assunto_classe = paste(cod_ass, cod_clas))
df <- df %>% mutate_all(~replace(., is.na(.), 0))
df <- df[,c(7,6,1,3)]

ass_class <- ass_class %>% left_join(df[,-c(3,4)], by = "cod_assunto_classe")
ass_class <- ass_class %>% mutate(valido = replace(valido, ocorrencia > 100, 1)) #Ajustando Problemas de inconsistência
ass_class <- ass_class %>% mutate(tipo_class_ass = 
                                    (ifelse(ocorrencia <= 5,1,
                                            ifelse(quartile <= 0.25,2,
                                                   ifelse(quartile <= 0.75,3,4)))))

#write.csv2(ass_class, "cod_ass_class_perc.csv")


#varas
tre_varas <- read.csv("tre_codigo_varas_classe_assunto.csv", header = TRUE, sep = ";")
trt_varas <- read.csv("trt_codigo_varas_classe_assunto.csv", header = TRUE, sep = ";")
tj_varas <- read.csv("tj_codigo_varas_classe_assunto.csv", header = TRUE, sep = ";")
trf_varas <- read.csv("trf_codigo_varas_classe_assunto.csv", header = TRUE, sep = ";")
tm_varas <- read.csv("tm_codigo_varas_classe_assunto.csv", header = TRUE, sep = ";")

varas <- trf_varas %>% rbind(tre_varas) %>% rbind(tj_varas) %>% 
  rbind(trt_varas) %>% rbind(tm_varas)
varas <-  varas %>% left_join(ass_class[,-c(4,5)], by = "cod_assunto_classe") %>% 
  select(c(8,9,5,3,2,6,7,11,12))
varas <- varas %>% group_by(dadosBasicos.orgaoJulgador.codigoOrgao) %>% 
  mutate_all(~replace(., is.na(.), 0)) %>% 
  mutate(ocorrencia = sum(ocorrencia)) %>% 
  mutate(valido = sum(valido)) %>% 
  mutate(nao_valido = n()-valido) %>% 
  select(-c(5,7)) %>% 
  group_by(dadosBasicos.orgaoJulgador.codigoOrgao, tipo_class_ass) %>% 
  mutate(count = n()) %>% distinct() %>% 
  spread(tipo_class_ass, count) %>% 
  mutate_all(~replace(., is.na(.), 0)) %>% 
  rename("freq1" = "1", "freq2" = "2","freq3" = "3","freq4" = "4")

write_csv(varas, "varas_valido.csv")
