#!/usr/bin/env python3
# coding=utf-8

"""
@version:0.1
@author: ysicing
@file: run.py
@time: 2017/2/28 下午11:25
"""

from app import create_app
app = create_app()

app.run(host="0.0.0.0",port=4000,debug=app.debug, threaded=True)