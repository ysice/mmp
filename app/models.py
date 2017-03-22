#!/usr/bin/env python3
# coding=utf-8

"""
@version:0.1
@author: ysicing
@file: models.py
@time: 2017/2/28 下午11:40
"""

from flask_sqlalchemy import SQLAlchemy
from sqlalchemy.exc import DatabaseError
from sqlalchemy.sql import func
from socket import inet_aton, inet_ntoa
from struct import unpack, pack, error as struct_error
from passlib.hash import bcrypt_sha256
#from flask import current_app as app
from flask_login import UserMixin
from . import login_manager



import datetime
import hashlib
import json

db = SQLAlchemy()


class Users(UserMixin,db.Model):
    __tablename__ = 'users'
    id = db.Column(db.Integer,primary_key=True)
    username = db.Column(db.String(128),unique=True,index=True)
    email = db.Column(db.String(128),unique=True,index=True)
    anhao = db.Column(db.TEXT)
    password = db.Column(db.String(128))
    verified = db.Column(db.BOOLEAN,default=False)
    admin = db.Column(db.BOOLEAN,default=False)
    joined = db.Column(db.DATETIME,default=datetime.datetime.utcnow)

    def __repr__(self):
        return '<User %r>' % self.username


@login_manager.user_loader
def load_user(user_id):
    return Users.query.get(int(user_id))