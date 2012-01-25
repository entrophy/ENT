#! /usr/bin/env python

import os;

root = os.path.abspath(os.path.realpath(__file__)).replace('/tools/libupdate.py', '');
lib = os.path.join(root, 'lib', '');
vendors = os.listdir(lib);

for vendor in vendors:
	remotes = os.listdir(lib+vendor+'/remotes/');
	for remote in remotes:
		remote_path = os.path.join(lib+vendor, 'remotes', remote, '');
		os.system('cd '+remote_path+' && git pull origin master');
