FROM python:3
ENV MY_PARAMETER=""
WORKDIR /api/
COPY requirements.txt ./
RUN apt-get -y update
RUN apt-get install -y python3-pip

RUN pip3 install -r requirements.txt

EXPOSE 81
CMD ["uvicorn","api:app","--reload", "--host", "0.0.0.0", "--port", "8080"]
