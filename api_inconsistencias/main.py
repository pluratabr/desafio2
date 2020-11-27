# -*- coding: utf-8 -*-
"""
Created on Wed Oct 28 01:36:03 2020

@author: Plurata - Buscador de Inconsistencias
"""

import graphene
from datetime import date, datetime, time, timedelta
from typing import Optional, List
from fastapi import Depends, FastAPI, Header, HTTPException
from sqlalchemy import desc
from sqlalchemy import BigInteger, Boolean, Integer, String, Numeric, Date, DateTime, Column, create_engine, ForeignKey
from sqlalchemy.orm import relationship, joinedload, subqueryload, Session
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from pydantic import BaseModel
from graphene_sqlalchemy import SQLAlchemyObjectType
from starlette.graphql import GraphQLApp

Base = declarative_base()
engine = create_engine('postgresql+psycopg2://postgres:123456@157.230.184.48/db_cnj')
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

    
    
class Processos(Base):
    __tablename__ = 'processos'
    ano = Column(Integer, primary_key=True)
    mes = Column(Integer, primary_key=True)
    justica = Column(String, primary_key=True)
    tribunal = Column(String, primary_key=True)
    numeroprocesso = Column(String, primary_key=True)
    processovinculado = Column(String, primary_key=True)
    flag_diretorio = Column(String, primary_key=True)
    grau = Column(String, primary_key=True)
    relacaoincidental = Column(String, primary_key=True)
    prioridade = Column(String, primary_key=True)
    orgaojulgadorcodigoorgao = Column(String, primary_key=True)
    orgaojulgadornomeorgao = Column(String, primary_key=True)
    orgaojulgadormunicipioibge = Column(Integer, primary_key=True)
    orgaojulgadormunicipio = Column(String, primary_key=True)
    orgaojulgadoruf = Column(String, primary_key=True)
    orgaojulgadorinstancia = Column(String, primary_key=True)
    competencia = Column(String, primary_key=True)
    classeprocessual = Column(Integer, primary_key=True)
    descricaoclasseprocessual = Column(String, primary_key=True)
    codigolocalidade = Column(String, primary_key=True)
    localidademunicipio = Column(String, primary_key=True)
    localidadeuf = Column(String, primary_key=True)
    nivelsigilo = Column(Integer, primary_key=True)
    intervencaomp = Column(String, primary_key=True)
    dataajuizamento = Column(Date, primary_key=True)
    procel = Column(String, primary_key=True)
    codigosistema = Column(Integer, primary_key=True)
    dscsistema = Column(String, primary_key=True)
    qtd_processo = Column(Integer)
    qtd_tamanhoprocesso = Column(Integer)
    vl_causa = Column(Numeric)
    qtd_dias = Column(Integer)
    qtd_transitadoemjulgado = Column(Integer)
    qtd_intimacoes = Column(Integer)
    qtd_movimentacoes = Column(Integer)
    qtd_assuntos = Column(Integer)
    qtd_mediaclasseprocessual = Column(Integer)
    qtd_medianomeorgao = Column(Integer)
    qtd_mediamunicipio = Column(Integer)
    qtd_mediauf = Column(Integer)
    qtd_mediainstancia = Column(Integer)
    qtd_mediasistema = Column(Integer)
    qtd_mediagrau = Column(Integer)
    qtd_media = Column(Integer)
    def to_dict(self):
        result = SAFRSBase.to_dict(self)
        return result


class Movimentos(Base):
    __tablename__ = 'movimentos'
    ano = Column(Integer, primary_key=True)
    mes = Column(Integer, primary_key=True)
    justica = Column(String, primary_key=True)
    tribunal = Column(String, primary_key=True)
    flag_diretorio = Column(String, primary_key=True)
    numeroprocesso = Column(String, primary_key=True)
    movimentoseq = Column(String, primary_key=True)
    tiporesponsavelmovimento = Column(String, primary_key=True)
    codigocomplementonacional = Column(Integer, primary_key=True)
    descricaocomplementonacional = Column(String, primary_key=True)
    orgaojulgadornome = Column(String, primary_key=True)
    orgaojulgadorcodigo = Column(Integer, primary_key=True)
    orgaojulgadormunicipiocodigo = Column(Integer, primary_key=True)
    orgaojulgadormunicipio = Column(String, primary_key=True)
    orgaojulgadoruf = Column(String, primary_key=True)
    orgaojulgadorinstancia = Column(String, primary_key=True)
    tipodecisao = Column(Integer, primary_key=True)
    nivelsigilo = Column(Integer, primary_key=True)
    datahora = Column(DateTime, primary_key=True)
    movimentonacionalcodigonacional = Column(Integer, primary_key=True)
    movimentonacionaldescricao = Column(String, primary_key=True)
    movimentolocalcodigomovimento = Column(Integer, primary_key=True)
    movimentolocalcodigopainacional = Column(Integer, primary_key=True)
    qtd_movimentonacional = Column(Integer)
    qtd_movimentolocal = Column(Integer)
    qtd_movimento = Column(Integer)
    qtd_dias = Column(Integer)

class Assuntos(Base):
    __tablename__ = 'assunto'
    ano = Column(Integer, primary_key=True)
    mes = Column(Integer, primary_key=True)
    justica = Column(String, primary_key=True)
    tribunal = Column(String, primary_key=True)
    numeroprocesso = Column(String, primary_key=True)
    processovinculado = Column(String, primary_key=True)
    flag_diretorio = Column(String, primary_key=True)
    grau = Column(String, primary_key=True)
    assuntoprincipal = Column(Boolean, primary_key=True)
    assuntonacionalcodigonacional = Column(Integer, primary_key=True)
    assuntonacionaldescricao = Column(String, primary_key=True)
    assuntolocalcodigoassunto = Column(BigInteger, primary_key=True)
    assuntolocalcodigopainacional = Column(Integer, primary_key=True)
    assuntolocaldescricao = Column(String, primary_key=True)
    relacaoincidental = Column(String, primary_key=True)
    prioridade = Column(String, primary_key=True)
    orgaojulgadorcodigoorgao = Column(String, primary_key=True)
    orgaojulgadornomeorgao = Column(String, primary_key=True)
    orgaojulgadormunicipioibge = Column(Integer, primary_key=True)
    orgaojulgadormunicipio = Column(String, primary_key=True)
    orgaojulgadoruf = Column(String, primary_key=True)
    orgaojulgadorinstancia = Column(String, primary_key=True)
    competencia = Column(String, primary_key=True)
    classeprocessual = Column(Integer, primary_key=True)
    descricaoclasseprocessual = Column(String, primary_key=True)
    codigolocalidade = Column(String, primary_key=True)
    localidademunicipio = Column(String, primary_key=True)
    localidadeuf = Column(String, primary_key=True)
    nivelsigilo = Column(Integer, primary_key=True)
    intervencaomp = Column(String, primary_key=True)
    dataajuizamento = Column(Date, primary_key=True)
    procel = Column(String, primary_key=True)
    codigosistema = Column(Integer, primary_key=True)
    dscsistema = Column(String, primary_key=True)
    qtd_processo = Column(Integer)

class ProcessosPorAssuntoNacional(Base):
    __tablename__ = 'vw_processos_ano_mes_assunto_nacional'
    ano = Column(Integer, primary_key=True)
    mes = Column(Integer, primary_key=True)
    assuntonacionaldescricao = Column(String, primary_key=True)
    qtd_processo = Column(Integer)

class ProcessosPorClasse(Base):
    __tablename__ = 'vw_processos_ano_descricaoclasseprocessual'
    ano = Column(Integer, primary_key=True)
    mes = Column(Integer, primary_key=True)
    descricaoclasseprocessual = Column(String, primary_key=True)
    qtd_processo = Column(Integer)

class RelatorioInconsistencia(Base):
    __tablename__ = 'relatorio_inconsistencia'
    cod_assunto_classe = Column(String, primary_key=True)
    tribunal = Column(String, primary_key=True)
    descricao_classe = Column(String, primary_key=True)
    dsc_tip_orgao = Column(String, primary_key=True)
    ocorrencia = Column(Numeric)
    percentual = Column(Numeric)

class RegraClasseAssunto(Base):
    __tablename__ = 'regra_classe_assunto'
    cod_assunto_classe = Column(String, primary_key=True)
    cod_classe = Column(String, primary_key=True)
    cod_assunto = Column(String, primary_key=True)
    valido = Column(Numeric)
    ocorrencia = Column(Numeric)

class EstatClasseAssunto(Base):
    __tablename__ = 'estat_classe_assunto'
    tribunal = Column(String, primary_key=True)
    justica = Column(String, primary_key=True)
    cod_assunto_classe = Column(String, primary_key=True)
    cod_classe = Column(String, primary_key=True)
    descricao_classe = Column(String, primary_key=True)
    cod_assunto = Column(String, primary_key=True)
    descricao_assunto = Column(String, primary_key=True)
    dsc_tip_orgao = Column(String, primary_key=True)
    ocorrencia = Column(Numeric)
    percentual = Column(Numeric)

class BaseModeloTJ3Clusters(Base):
    __tablename__ = 'base_modelo_tj_3clusters'
    vara = Column(Integer, primary_key=True)
    ocorrencia = Column(Integer, primary_key=True)
    valido = Column(Integer, primary_key=True)
    nao_valido = Column(Integer, primary_key=True)
    freq1 = Column(Integer, primary_key=True)
    freq2 = Column(Integer, primary_key=True)
    freq3 = Column(Integer, primary_key=True)
    freq4 = Column(Integer, primary_key=True)
    previsao_ward_3k = Column(Integer)
    previsao_kmeans_3k = Column(Integer)

class BaseModeloTM4Clusters(Base):
    __tablename__ = 'base_modelo_tm_4clusters'
    vara = Column(Integer, primary_key=True)
    ocorrencia = Column(Integer, primary_key=True)
    valido = Column(Integer, primary_key=True)
    nao_valido = Column(Integer, primary_key=True)
    freq1 = Column(Integer, primary_key=True)
    freq2 = Column(Integer, primary_key=True)
    freq3 = Column(Integer, primary_key=True)
    freq4 = Column(Integer, primary_key=True)
    previsao_ward_4k = Column(Integer)
    previsao_kmeans_4k = Column(Integer)


class BaseModeloTRE4Clusters(Base):
    __tablename__ = 'base_modelo_tre_4clusters'
    vara = Column(Integer, primary_key=True)
    ocorrencia = Column(Integer, primary_key=True)
    valido = Column(Integer, primary_key=True)
    nao_valido = Column(Integer, primary_key=True)
    freq1 = Column(Integer, primary_key=True)
    freq2 = Column(Integer, primary_key=True)
    freq3 = Column(Integer, primary_key=True)
    freq4 = Column(Integer, primary_key=True)
    previsao_ward_4k = Column(Integer)
    previsao_kmeans_4k = Column(Integer)

class BaseModeloTRT2Clusters(Base):
    __tablename__ = 'base_modelo_trt_2clusters'
    vara = Column(Integer, primary_key=True)
    ocorrencia = Column(Integer, primary_key=True)
    valido = Column(Integer, primary_key=True)
    nao_valido = Column(Integer, primary_key=True)
    freq1 = Column(Integer, primary_key=True)
    freq2 = Column(Integer, primary_key=True)
    freq3 = Column(Integer, primary_key=True)
    freq4 = Column(Integer, primary_key=True)
    previsao_ward_2k = Column(Integer)
    previsao_kmeans_2k = Column(Integer)


class JsonProcessos(BaseModel):
#    __tablename__ = 'parent'
    ano : Optional[int]
    mes : Optional[int]
    justica: Optional[str]
    tribunal: Optional[str]
    numeroprocesso: Optional[str]
    processovinculado: Optional[str]
    flag_diretorio: Optional[str]
    grau: Optional[str]
    relacaoincidental: Optional[str]
    prioridade: Optional[str]
    orgaojulgadorcodigoorgao: Optional[str]
    orgaojulgadornomeorgao: Optional[str]
    orgaojulgadormunicipioibge : Optional[int]
    orgaojulgadormunicipio: Optional[str]
    orgaojulgadoruf: Optional[str]
    orgaojulgadorinstancia: Optional[str]
    competencia: Optional[str]
    classeprocessual : Optional[int]
    descricaoclasseprocessual: Optional[str]
    codigolocalidade: Optional[str]
    localidademunicipio: Optional[str]
    localidadeuf: Optional[str]
    nivelsigilo : Optional[int]
    intervencaomp: Optional[str]
    dataajuizamento : Optional[date]
    procel: Optional[str]
    codigosistema : Optional[int]
    dscsistema: Optional[str]
    qtd_processo : Optional[int]
    qtd_tamanhoprocesso : Optional[int]
    vl_causa : Optional[float]
    qtd_dias : Optional[int]
    qtd_transitadoemjulgado : Optional[int]
    qtd_intimacoes : Optional[int]
    qtd_movimentacoes : Optional[int]
    qtd_assuntos : Optional[int]
    qtd_mediaclasseprocessual : Optional[int]
    qtd_medianomeorgao : Optional[int]
    qtd_mediamunicipio : Optional[int]
    qtd_mediauf : Optional[int]
    qtd_mediainstancia : Optional[int]
    qtd_mediasistema : Optional[int]
    qtd_mediagrau : Optional[int]
    qtd_media : Optional[int]

    class Config:
        orm_mode = True


class JsonMovimentos(BaseModel):
    ano: Optional[int]
    mes: Optional[int]
    justica: Optional[str]
    tribunal: Optional[str]
    flag_diretorio: Optional[str]
    numeroprocesso: Optional[str]
    movimentoseq: Optional[str]
    tiporesponsavelmovimento: Optional[str]
    codigocomplementonacional: Optional[int]
    descricaocomplementonacional: Optional[str]
    orgaojulgadornome: Optional[str]
    orgaojulgadorcodigo: Optional[int]
    orgaojulgadormunicipiocodigo: Optional[int]
    orgaojulgadormunicipio: Optional[str]
    orgaojulgadoruf: Optional[str]
    orgaojulgadorinstancia: Optional[str]
    tipodecisao: Optional[int]
    nivelsigilo: Optional[int]
    datahora: Optional[datetime]
    movimentonacionalcodigonacional: Optional[int]
    movimentonacionaldescricao: Optional[str]
    movimentolocalcodigomovimento: Optional[int]
    movimentolocalcodigopainacional: Optional[int]
    qtd_movimentonacional: Optional[int]
    qtd_movimentolocal: Optional[int]
    qtd_movimento: Optional[int]
    qtd_dias: Optional[int]

    class Config:
        orm_mode = True


from typing import cast
from pydantic import BaseModel, ConstrainedInt

    
    
class JsonAssuntos(BaseModel):
    ano: Optional[int]
    mes: Optional[int]
    justica: Optional[str]
    tribunal: Optional[str]
    numeroprocesso: Optional[str]
    processovinculado: Optional[str]
    flag_diretorio: Optional[str]
    grau: Optional[str]
    assuntoprincipal: Optional[bool]
    assuntonacionalcodigonacional: Optional[int]
    assuntonacionaldescricao: Optional[str]
    assuntolocalcodigoassunto: Optional[int]
    assuntolocalcodigopainacional: Optional[int]
    assuntolocaldescricao: Optional[str]
    relacaoincidental: Optional[str]
    prioridade: Optional[str]
    orgaojulgadorcodigoorgao: Optional[str]
    orgaojulgadornomeorgao: Optional[str]
    orgaojulgadormunicipioibge: Optional[int]
    orgaojulgadormunicipio: Optional[str]
    orgaojulgadoruf: Optional[str]
    orgaojulgadorinstancia: Optional[str]
    competencia: Optional[str]
    classeprocessual: Optional[int]
    descricaoclasseprocessual: Optional[str]
    codigolocalidade: Optional[str]
    localidademunicipio: Optional[str]
    localidadeuf: Optional[str]
    nivelsigilo: Optional[int]
    intervencaomp: Optional[str]
    dataajuizamento: Optional[date]
    procel: Optional[str]
    codigosistema: Optional[int]
    dscsistema: Optional[str]
    qtd_processo: Optional[int]
    class Config:
        orm_mode = True

class JsonRelatorioInconsistencia(BaseModel):
    cod_assunto_classe: Optional[str]
    tribunal: Optional[str]
    descricao_classe: Optional[str]
    dsc_tip_orgao: Optional[str]
    ocorrencia : Optional[float]
    percentual: Optional[float]
    class Config:
        orm_mode = True

class JsonRegraClasseAssunto(BaseModel):
    cod_assunto_classe: Optional[str]
    cod_classe: Optional[str]
    cod_assunto: Optional[str]
    valido: Optional[int]
    ocorrencia: Optional[int]
    class Config:
        orm_mode = True

class JsonEstatClasseAssunto(BaseModel):
    
    tribunal: Optional[str]
    justica: Optional[str]
    cod_assunto_classe: Optional[str]
    cod_classe: Optional[str]
    descricao_classe: Optional[str]
    cod_assunto: Optional[str]
    descricao_assunto: Optional[str]
    dsc_tip_orgao: Optional[str]
    ocorrencia : Optional[float]
    percentual: Optional[float]
    class Config:
        orm_mode = True

class JsonProcessosPorAssuntoNacional(BaseModel):
    ano: Optional[str]
    mes: Optional[str]
    assuntonacionaldescricao: Optional[str]
    total: Optional[int]
    class Config:
        orm_mode = True

class JsonProcessosPorAssuntoNacional(BaseModel):
    ano: Optional[str]
    mes: Optional[str]
    assuntonacionaldescricao: Optional[str]
    qtd_processo: Optional[int]
    class Config:
        orm_mode = True

class JsonProcessosPorClasse(BaseModel):
    ano: Optional[str]
    mes: Optional[str]
    descricaoclasseprocessual: Optional[str]
    qtd_processo: Optional[int]
    class Config:
        orm_mode = True


class JsonBaseModeloTJ3Clusters(BaseModel):
    vara: Optional[int]
    ocorrencia: Optional[int]
    valido: Optional[int]
    nao_valido: Optional[int]
    freq1: Optional[int]
    freq2: Optional[int]
    freq3: Optional[int]
    freq4: Optional[int]
    previsao_ward_3k: Optional[int]
    previsao_kmeans_3k: Optional[int]
    class Config:
        orm_mode = True

class JsonBaseModeloTM4Clusters(BaseModel):
    vara: Optional[int]
    ocorrencia: Optional[int]
    valido: Optional[int]
    nao_valido: Optional[int]
    freq1: Optional[int]
    freq2: Optional[int]
    freq3: Optional[int]
    freq4: Optional[int]
    previsao_ward_4k: Optional[int]
    previsao_kmeans_4k: Optional[int]
    class Config:
        orm_mode = True

class JsonBaseModeloTRE4Clusters(BaseModel):
    vara: Optional[int]
    ocorrencia: Optional[int]
    valido: Optional[int]
    nao_valido: Optional[int]
    freq1: Optional[int]
    freq2: Optional[int]
    freq3: Optional[int]
    freq4: Optional[int]
    previsao_ward_4k: Optional[int]
    previsao_kmeans_4k: Optional[int]
    class Config:
        orm_mode = True

class JsonBaseModeloTRT2Clusters(BaseModel):
    vara: Optional[int]
    ocorrencia: Optional[int]
    valido: Optional[int]
    nao_valido: Optional[int]
    freq1: Optional[int]
    freq2: Optional[int]
    freq3: Optional[int]
    freq4: Optional[int]
    previsao_ward_2k: Optional[int]
    previsao_kmeans_2k: Optional[int]
    class Config:
        orm_mode = True


class QueryProcessos(graphene.ObjectType):
    users = graphene.List(Processos)

    def resolve_users(self, info):
        query = Processos.get_query(info)  # SQLAlchemy query
        return query.all()

class QueryMovimentos(graphene.ObjectType):
    users = graphene.List(Movimentos)

    def resolve_users(self, info):
        query = Movimentos.get_query(info)  # SQLAlchemy query
        return query.all()

class QueryAssuntos(graphene.ObjectType):
    assuntos = graphene.List(Assuntos)

    def resolve_assuntos(self, info):
        query = Assuntos.get_query(info)  # SQLAlchemy query
        return query.all()


app = FastAPI()

# Dependency
def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()
        
def get_movimentos(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 100):
    return db.query(Movimentos).offset(skip).limit(limit).all()
        
def get_processos(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 100):
    return db.query(Processos).offset(skip).limit(limit).all()
        
def get_assuntos(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 100):
    return db.query(Assuntos).offset(skip).limit(limit).all()

def get_BaseModeloTJ3Clusters(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 10000):
    return db.query(BaseModeloTJ3Clusters).offset(skip).limit(limit).all()

def get_BaseModeloTM4Clusters(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 10000):
    return db.query(BaseModeloTM4Clusters).offset(skip).limit(limit).all()

def get_BaseModeloTRE4Clusters(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 10000):
    return db.query(BaseModeloTRE4Clusters).offset(skip).limit(limit).all()

def get_BaseModeloTRT2Clusters(db: Session, skip: Optional[int] = 0, limit: Optional[int] = 10000):
    return db.query(BaseModeloTRT2Clusters).offset(skip).limit(limit).all()



def get_validacoes(db, tribunal, justica, cod_assunto_classe, cod_classe, descricao_classe, cod_assunto, descricao_assunto, dsc_tip_orgao, percentual, skip, limit):
    q = db.query(EstatClasseAssunto)
    if (tribunal):
        q = q.filter(EstatClasseAssunto.tribunal == tribunal)
    if (justica):
        q = q.filter(EstatClasseAssunto.justica == justica)
    if (cod_assunto_classe):
        q = q.filter(EstatClasseAssunto.cod_assunto_classe == cod_assunto_classe)
    if (cod_classe):
        q = q.filter(EstatClasseAssunto.cod_classe == cod_classe)
    if (descricao_classe):
        q = q.filter(EstatClasseAssunto.descricao_classe == descricao_classe)
    if (cod_assunto):
        q = q.filter(EstatClasseAssunto.cod_assunto == cod_assunto)
    if (descricao_assunto):
        q = q.filter(EstatClasseAssunto.descricao_assunto == descricao_assunto)
    print(percentual)
    if (percentual > 0):
        print(percentual)
        q = q.filter(EstatClasseAssunto.percentual >= percentual)
    if (dsc_tip_orgao):
        q = q.filter(EstatClasseAssunto.dsc_tip_orgao == dsc_tip_orgao)
    q = q.order_by(desc(EstatClasseAssunto.percentual))

    return q.offset(skip).limit(limit).all()
        
def get_relatorio_inconsistencias(db: Session, cod_assunto_classe: Optional[str] = None,
    tribunal: Optional[str] = None,
    descricao_classe: Optional[str] = None,
    dsc_tip_orgao: Optional[str] = None, skip: Optional[int] = 0, limit: Optional[int] = 100):
    q = db.query(RelatorioInconsistencia)
    if (tribunal):
        q = q.filter(RelatorioInconsistencia.tribunal == tribunal)
    if (cod_assunto_classe):
        q = q.filter(RelatorioInconsistencia.cod_assunto_classe == cod_assunto_classe)
    if (descricao_classe):
        q = q.filter(RelatorioInconsistencia.descricao_classe == descricao_classe)
    if (dsc_tip_orgao):
        q = q.filter(RelatorioInconsistencia.dsc_tip_orgao == dsc_tip_orgao)
    q = q.order_by(desc(RelatorioInconsistencia.percentual))

    return q.offset(skip).limit(limit).all()
        
def get_regra_classe_assunto(db: Session, cod_assunto_classe: Optional[str] = None,
    cod_assunto: Optional[str] = None,
    cod_classe: Optional[str] = None, skip: Optional[int] = 0, limit: Optional[int] = 100):
    q = db.query(RegraClasseAssunto)
    if (cod_assunto_classe):
        q = q.filter(RegraClasseAssunto.cod_assunto_classe == cod_assunto_classe)
    if (cod_classe):
        q = q.filter(RegraClasseAssunto.cod_classe == cod_classe)
    if (cod_assunto):
        q = q.filter(RegraClasseAssunto.cod_assunto == cod_assunto)
    q = q.order_by(desc(RegraClasseAssunto.ocorrencia))

    return q.offset(skip).limit(limit).all()
        
def get_processos_por_assunto_nacional(db: Session, ano: Optional[str] = None,
    mes: Optional[str] = None,
    skip: Optional[int] = 0, limit: Optional[int] = 100):
    q = db.query(ProcessosPorAssuntoNacional)
    if (ano):
        q = q.filter(ProcessosPorAssuntoNacional.ano == ano)
    if (mes):
        q = q.filter(ProcessosPorAssuntoNacional.mes == mes)

    return q.offset(skip).limit(limit).all()
        
def get_processos_por_classe(db: Session, ano: Optional[str] = None,
    mes: Optional[str] = None,
    skip: Optional[int] = 0, limit: Optional[int] = 100):
    q = db.query(ProcessosPorClasse)
    if (ano):
        q = q.filter(ProcessosPorClasse.ano == ano)
    if (mes):
        q = q.filter(ProcessosPorClasse.mes == mes)

    return q.offset(skip).limit(limit).all()
        



@app.get("/api/movimentos/", response_model=List[JsonMovimentos])
def read_movimentos(skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    movimentos = get_movimentos(db, skip=skip, limit=limit)
    return movimentos

@app.get("/api/processos/", response_model=List[JsonProcessos])
def read_processos(skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    processos = get_processos(db, skip=skip, limit=limit)
    return processos

@app.get("/api/assuntos/", response_model=List[JsonAssuntos])
def read_assuntos(skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    assuntos = get_assuntos(db, skip=skip, limit=limit)
    return assuntos

@app.get("/api/relatorio/", response_model=List[JsonRelatorioInconsistencia])
def read_relatorio_inconsistencias(cod_assunto_classe: Optional[str] = None,
    tribunal: Optional[str] = None,
    descricao_classe: Optional[str] = None,
    dsc_tip_orgao: Optional[str] = None, skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    relatorio_inconsistencias = get_relatorio_inconsistencias(db, cod_assunto_classe, tribunal, descricao_classe, dsc_tip_orgao, skip=skip, limit=limit)
    return relatorio_inconsistencias


@app.get("/api/valida/", response_model=List[JsonEstatClasseAssunto])
def read_valida(tribunal : Optional[str] = None, justica : Optional[str] = None, cod_assunto_classe : Optional[str] = None, cod_classe : Optional[str] = None, descricao_classe : Optional[str] = None, cod_assunto : Optional[str] = None, descricao_assunto : Optional[str] = None, dsc_tip_orgao : Optional[str] = None, percentual : Optional[float] = 0, skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    validacoes = get_validacoes(db, tribunal, justica, cod_assunto_classe, cod_classe, descricao_classe, cod_assunto, descricao_assunto, dsc_tip_orgao, percentual, skip=skip, limit=limit)
    return validacoes


@app.get("/api/regraClasseAssunto/", response_model=List[JsonRegraClasseAssunto])
def read_regra_classe_assunto(cod_assunto_classe: Optional[str] = None,
    cod_assunto: Optional[str] = None,
    cod_classe: Optional[str] = None, skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    regra_classe_assuntos = get_regra_classe_assunto(db, cod_assunto_classe, cod_assunto, cod_classe, skip=skip, limit=limit)
    return regra_classe_assuntos

@app.get("/api/totalProcessosPorAssuntoNacional/", response_model=List[JsonProcessosPorAssuntoNacional])
def read_processos_por_assunto_nacional(ano: Optional[str] = None,
    mes: Optional[str] = None,
    skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    processos_por_assunto_nacional = get_processos_por_assunto_nacional(db, ano, mes, skip=skip, limit=limit)
    return processos_por_assunto_nacional

@app.get("/api/totalProcessosPorClasse/", response_model=List[JsonProcessosPorClasse])
def read_processos_por_classe(ano: Optional[str] = None,
    mes: Optional[str] = None,
    skip: Optional[int] = 0, limit: Optional[int] = 100, db: Session = Depends(get_db)):
    processos_por_classe = get_processos_por_classe(db, ano, mes, skip=skip, limit=limit)
    return processos_por_classe

@app.get("/api/base_modelo_tj_3clusters/", response_model=List[JsonBaseModeloTJ3Clusters])
def read_base_modelo_tj_3clusters(skip: Optional[int] = 0, limit: Optional[int] = 10000, db: Session = Depends(get_db)):
    base_modelo_tj_3clusters = get_BaseModeloTJ3Clusters(db, skip=skip, limit=limit)
    return base_modelo_tj_3clusters

@app.get("/api/base_modelo_tm_4clusters/", response_model=List[JsonBaseModeloTM4Clusters])
def read_base_modelo_tm_4clusters(skip: Optional[int] = 0, limit: Optional[int] = 10000, db: Session = Depends(get_db)):
    base_modelo_tm_4clusters = get_BaseModeloTM4Clusters(db, skip=skip, limit=limit)
    return base_modelo_tm_4clusters

@app.get("/api/base_modelo_tre_4clusters/", response_model=List[JsonBaseModeloTRE4Clusters])
def read_base_modelo_tre_4clusters(skip: Optional[int] = 0, limit: Optional[int] = 10000, db: Session = Depends(get_db)):
    base_modelo_tre_4clusters = get_BaseModeloTRE4Clusters(db, skip=skip, limit=limit)
    return base_modelo_tre_4clusters

@app.get("/api/base_modelo_trt_2clusters/", response_model=List[JsonBaseModeloTRT2Clusters])
def read_base_modelo_trt_2clusters(skip: Optional[int] = 0, limit: Optional[int] = 10000, db: Session = Depends(get_db)):
    base_modelo_trt_2clusters = get_BaseModeloTRT2Clusters(db, skip=skip, limit=limit)
    return base_modelo_trt_2clusters

