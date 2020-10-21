library(reshape2)
library(broom)
library(rsample)
library(dplyr)
library(readr)
library(magrittr)


rm(list=ls())

getwd()
# lendo a base
data <- read.csv("df10.csv", header = TRUE, sep = ";")
head(data)

data$tribunal <- as.factor(data$tribunal)
data$categoria <- as.factor(data$categoria)
data$cl_principal <- as.factor(data$cl_principal)
data$valid_ac <- as.factor(data$valid_ac)
data$cod_assunto_classe <- as.factor(data$cod_assunto_classe)
data$ocorrencia_ac <- as.numeric(data$ocorrencia_ac)
data$cod_assunto <- as.factor(data$cod_assunto)

str(data)

# dividindo a base em treino e teste 
amostra <- sample(2, nrow(data), replace = T, prob=c(0.8, 0.2))
data_treino2 <- data[amostra == 1,]
data_teste2 <- data[amostra==2,]

nrow(data_treino2)
nrow(data_teste2)

table(data_treino2$categoria)


# balanceando base treino
acerto2 <- subset(data, data$categoria == 0)
erro2 <- subset(data, data$categoria == 1)

aux <- sample(1:nrow(acerto2), nrow(acerto2), replace = FALSE)
acerto2["aux"] <- aux
acerto3 <- acerto2[acerto2$aux<=nrow(erro2),]
acerto3 <- acerto3[,-ncol(acerto3)]
base_balanceada2 <- rbind(acerto3, erro2)
head(base_balanceada2)
dim(base_balanceada2)
dim(data_teste2)


# modelagem 

modelo1 <- glm(categoria ~ tribunal + cl_principal + ocorrencia_ac, data = base_balanceada, family = "binomial")
summary(modelo1)

data_teste$RESULT <- predict(modelo1, newdata = data_teste, type="response")
data_teste$SITUACAO <- (data_teste$RESULT*100 > 50)
data_teste$SITUACAO <- ifelse(data_teste$SITUACAO == T, 1, 0)
(confusao <- table(Real = data_teste$categoria, Previsto = data_teste$SITUACAO))
(acuracia <- (sum(diag(confusao))/sum(confusao))*100)
(sensibilidade <- (confusao[4]/sum(confusao[2,])*100))
(especificidade <- (confusao[1]/sum(confusao[1,])*100))


modelo2 <- glm(categoria ~ DSC_TIP_ORGAO + cl_principal + ocorrencia_ac, data = base_balanceada2, family = "binomial")
summary(modelo2)

data_teste2$RESULT <- predict(modelo2, newdata = data_teste2, type="response")
data_teste2$SITUACAO <- (data_teste2$RESULT*100 > 50)
data_teste2$SITUACAO <- ifelse(data_teste2$SITUACAO == T, 1, 0)
(confusao <- table(Real = data_teste2$categoria, Previsto = data_teste2$SITUACAO))
(acuracia <- (sum(diag(confusao))/sum(confusao))*100)
(especificidade <- (confusao[4]/sum(confusao[2,])*100))
(sensibilidade <- (confusao[1]/sum(confusao[1,])*100))

