#!/usr/bin/env python3
# coding=utf-8

"""
@version:0.1
@author: ysicing
@file: utils.py
@time: 2017/2/28 下午11:40
"""

from flask import current_app as app,g,redirect,request,url_for,session,render_template,abort
from app.models import db,Config
from sqlalchemy.engine.url import make_url
from sqlalchemy import create_engine
from flask_cache import Cache
import logging
import hashlib
from six.moves.urllib.parse import urlparse, urljoin
import six
from six import moves
from werkzeug.utils import secure_filename
from functools import wraps
from itsdangerous import Signer,BadSignature

import requests
cache = Cache()
"""
error
"""
def init_errors(app):
    @app.errorhandler(404)
    def page_not_found(error):
        return render_template('errors/404.html'),404

    @app.errorhandler(403)
    def forbidden(error):
        return render_template('errors/403.html'),403

    @app.errorhandler(500)
    def net_error(error):
        return render_template('errors/500.html'),500

    @app.errorhandler(502)
    def gate_error(error):
        return render_template('errors/502.html'),502


def init_utils(app):
    app.jinja_env.globals.update(blog_name=blog_name)

@cache.memoize()
def is_setup():
    setup = Config.query.filter_by(key='setup').first()
    if setup:
        return setup.value
    else:
        return False

def sha512(strcode):
    return hashlib.sha512(strcode).hexdigest()

@cache.memoize()
def get_config(key):
    config = Config.query.filter_by(key=key).first()
    try:
        if config and config.value:
            value = config.value
            if value and value.isdigit():  #isdigit num yes or no
                return int(value)
            elif value and isinstance(value, six.string_types):
                if value.lower() == 'true':
                    return True
                elif value.lower() == 'false':
                    return False
                else:
                    return value
        else:
            set_config(key, None)
            return None
    except:
        print('error')

def set_config(key,value):
    config = Config.query.filter_by(key=key).first()
    if config:
        config.value = value
    else:
        config = Config(key,value)
        db.session.add(config)
    db.session.commit()
    return config

@cache.memoize()
def blog_name():
    name = get_config('blog_name')
    return name if name else 'Blog'


def authed():
    return bool(session.get('uid', False))

def is_safe_url(target):
    ref_url = urlparse(request.host_url)
    test_url = urlparse(request.host_url, target)
    return test_url.scheme in ('http','https') and ref_url.netloc == test_url.netloc

def is_admin():
    if authed():
        return session['admin']
    else:
        return False

def admins_only(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if session.get('admin'):
            return f(*args, **kwargs)
        else:
            return redirect(url_for('auth.oauth'))
    return decorated_function