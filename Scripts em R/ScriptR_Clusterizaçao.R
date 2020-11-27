# HACKATHON CNJ 
# clusterização

library(cluster)

getwd()
setwd("C:\\Users\\Rafaela\\Desktop\\HACKATHON CNJ")


base_modelo <- read.csv("base_trf.csv", header = TRUE, sep = ";")

str(base_modelo)

base_modelo$tribunal <- as.factor(base_modelo$tribunal)
base_modelo$justica <- as.factor(base_modelo$justica)
base_modelo$siglaTribunal <- as.factor(base_modelo$siglaTribunal)
base_modelo$dadosBasicos.orgaoJulgador.codigoOrgao <- as.character(base_modelo$dadosBasicos.orgaoJulgador.codigoOrgao)
base_modelo$ocorrencia <- as.numeric(base_modelo$ocorrencia)
base_modelo$valido <- as.numeric(base_modelo$valido)
base_modelo$nao_valido <- as.numeric(base_modelo$nao_valido)
base_modelo$v1 <- as.numeric(base_modelo$v1)
base_modelo$v2 <- as.numeric(base_modelo$v2)
base_modelo$v3 <- as.numeric(base_modelo$v3)
base_modelo$v4 <- as.numeric(base_modelo$v4)


row.names(base_modelo) <- base_modelo$dadosBasicos.orgaoJulgador.codigoOrgao
base_modelo <- base_modelo[, -c(1:4)]



# ---------- MÉTODO WARD ---------- #



set.seed(314)

# 4 CLUSTERS
modelo <- scale(base_modelo)
distancias <- dist(modelo, method = "euclidean")
grupos <- hclust(distancias, method = "ward.D")
plot(grupos, hang = -1)
plot(grupos, hang = -1, main="Cluster Dendogram - 4 clusters")

# "Forçando" 4 grupos, o dendograma indica 2
rect.hclust(grupos, k=4, border = 2:5) 
previsao <- cutree(grupos, 4)
base_modelo$previsao_ward_4K <- previsao
head(base_modelo)



# # 5 CLUSTERS
modelo <- scale(base_modelo)
distancias <- dist(modelo, method = "euclidean")
grupos <- hclust(distancias, method = "ward.D")
plot(grupos, hang = -1)
plot(grupos, hang = -1, main="Cluster Dendogram - 5 clusters")

# # "Forçando" 5 grupos, o dendograma indica 2
rect.hclust(grupos, k=5, border = 2:5) 
previsao <- cutree(grupos, 5)
base_modelo$previsao_ward_5K <- previsao
head(base_modelo)





# ---------- MÉTODO k-means ---------- #

# 4 CLUSTERS
k <- kmeans(modelo, 4)
previsao <- k$cluster
clusplot(modelo, previsao, color = T, lines = F, labels = TRUE)
base_modelo$previsao_kmeans_4K <- previsao

names(k)
k$betweenss
k$withinss
variacao <- kmeans(modelo, 1)$betweenss
for (i in 2:20){variacao[i] <- kmeans(modelo, i)$betweenss}
plot(1:20, variacao, type="b", xlab = "Número de clusters", ylab = "Soma de quadrados intergrupos", 
     main = "Simulação do número de clusters com maior variação entre os grupos")



# 5 CLUSTERS
k2 <- kmeans(modelo, 5)
previsao2 <- k$cluster
clusplot(modelo, previsao2, color = T, lines = F, labels = TRUE)
base_modelo$previsao_kmeans_5K <- previsao2
head(base_modelo)

names(k2)
k2$betweenss
k2$withinss
variacao <- kmeans(modelo, 1)$betweenss
for (i in 2:20){variacao[i] <- kmeans(modelo, i)$betweenss}
plot(1:20, variacao, type="b", xlab = "Número de clusters", ylab = "Soma de quadrados intergrupos", 
     main = "Simulação do número de clusters com maior variação entre os grupos")


head(base_modelo)
write.table(base_modelo, file = "base_modelo_comclusters.csv", sep = ";")




# ---------- Análise dos métodos ---------- #
prop.table(table(base_modelo$previsao_ward_4K))
prop.table(table(base_modelo$previsao_ward_5K))

prop.table(table(base_modelo$previsao_kmeans_4K))
prop.table(table(base_modelo$previsao_kmeans_5K))


base_modelo$cluster <- base_modelo$previsao_kmeans_4K
table(base_modelo$cluster)


prop.table(table(base_modelo$previsao_kmeans_4K, ))