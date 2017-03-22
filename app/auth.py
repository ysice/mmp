#!/usr/bin/env python3
# coding=utf-8

"""
@version:0.1
@author: ysicing
@file: auth.py
@time: 2017/2/28 下午11:40
"""

from flask import render_template,render_template_string,flash,request,redirect,abort,url_for,session,Blueprint
from app.models import db,Users,DSF
from itsdangerous import TimedSerializer,BadTimeSignature
from app.utils import sha512,authed,get_config,is_safe_url
from passlib.hash import bcrypt_sha256
from passlib.apps import custom_app_context as pwd_context
from flask import current_app as app
#from flask_security import Security

from rauth.service import OAuth2Service

import os

auth = Blueprint('auth',__name__)
#Security(app)



@auth.route('/oauth',methods=['POST','GET'])
def oauth():
    if request.method == 'POST':
        errors = []
        name = request.form['name']
        iname = Users.query.filter_by(username=name).first()
        if iname:
            try:
                status = bcrypt_sha256.verify(request.form['password'],iname.password)
            except:
                hash = pwd_context.encrypt(request.form['password'])
                status = pwd_context.verify(iname.password,hash)
            if status:
                try:
                    session.regenerate()
                except:
                    pass
                session['username'] = iname.username
                session['uid'] = iname.uid
                session['admin'] = iname.admin
                session['nonce'] = sha512(os.urandom(10))
                db.session.close()


                if request.args.get('next') and is_safe_url(request.args.get('next')):
                    return redirect(request.args.get('next'))
                return redirect(url_for('admin.adminds'))

            else:
                errors.append("Incorrect Username or password .")
                db.session.close()
                return render_template('login.html', errors=errors)
        else:
            errors.append("Not exist or Forbidden")
            db.session.close()
            return render_template('login.html', errors=errors)
    else:
        db.session.close()
        return render_template('login.html')

@auth.route('/logout')
def logout():
    if authed():
        session.clear()
    return redirect(url_for('views.static_html'))

@auth.route('/oauth/github')
def oaurh_github():
    redirect_uri = url_for('auth.oauth_github_call', next=request.args.get('next') or
        request.referrer or None, _external=True)
    print(redirect_uri)
    # More scopes http://developer.github.com/v3/oauth/#scopes
    params = {'redirect_uri': redirect_uri, 'scope': 'user:email'}
    print(params)
    print(github.get_authorize_url(**params))
    return redirect(github.get_authorize_url(**params))

@auth.route('/oauth/callback')
def oauth_github_call():
    if not 'code' in request.args:
        flash('Not authorize')
        return redirect(url_for('auth.oauth'))
    redirect_url = url_for('auth.oauth_github_call', _external=True)
    data = dict(
        code = request.args['code'],
        redirect_url = redirect_url,
        scope = 'user:email,public_repo'
    )
    auth = github.get_auth_session(data=data)
    respon = auth.get('user').json()
    user = DSF.get_or_create(respon['login'],respon['name'])
    session['token'] = auth.access_token
    session['user_id'] = user.id
    flash('Logged in as' + respon['name'])
    return redirect(url_for('auth.auth_test'))

@auth.route('/oauth/test')
def auth_test():
    if session.get('token'):
        print(session['token'])
        auth = github.get_session(token = session['token'])
        resp = auth.get('/user')
        if resp.status_code == 200:
            user = resp.json()
        return render_template('github.html',user=user)
    else:
        return redirect(url_for('auth.oauth'))

