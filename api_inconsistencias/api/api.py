from fastapi import FastAPI, BackgroundTasks
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from fastapi.openapi.utils import get_openapi
from sqlalchemy import create_engine




app = FastAPI()


origins = [
    "http://hackathon.plurata.com.br",
    "https://hackathon.plurata.com.br",
]

app.add_middleware(
    CORSMiddleware,
    allow_origins=origins,
    allow_origin_regex='https://.*\.plurata\.com\.br',
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


DATABASE_URL = 'postgresql+psycopg2://postgres:123456@157.230.184.48:5432/db_cnj'
db_connect = create_engine(DATABASE_URL, pool_recycle=3600)


@app.get("/inconsistencia/cod_orgao/{tribunal}")
def busca(tribunal):
    conn = db_connect.connect()
    query = conn.execute("select tribunal, corrigidos, sem_correcao, total as total_processos, round(per_corrigido, 2) as perc_corrigido from public.corecao_orgao where tribunal='{0}'".format(tribunal))
    result = [dict(zip(tuple(query.keys()), i)) for i in query.cursor]
    return {"registros": result}

@app.get("/labels/cod_orgao")
def busca():
    conn = db_connect.connect()
    query = conn.execute("select distinct tribunal from public.corecao_orgao")
    result=[]
    [result.append(i[0]) for i in query.cursor]
    return {"labels": result}


@app.get("/inconsistencia/num_processo/{tribunal}")
def busca(tribunal):
    conn = db_connect.connect()
    query = conn.execute("select tribunal, inconsistencia, count(processo) as n_inconsistencia from processos_inconsistentes where tribunal='{0}' group by tribunal, inconsistencia ".format(tribunal))
    result = [dict(zip(tuple(query.keys()), i)) for i in query.cursor]
    return {"registros": result}

@app.get("/labels/num_processo")
def busca():
    conn = db_connect.connect()
    query = conn.execute("select distinct tribunal from public.processos_inconsistentes")
    result=[]
    [result.append(i[0]) for i in query.cursor]
    return {"labels": result}


