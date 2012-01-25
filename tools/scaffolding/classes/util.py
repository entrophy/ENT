import os
import inspect
import string
import sys

def base():
	return os.path.abspath(inspect.getfile(inspect.currentframe())).replace("/scaffolding", "")

def root():
	return os.path.abspath(os.path.realpath(inspect.getfile(inspect.currentframe()))).replace('/tools/scaffolding/classes/util.py', '');

def module_base():
	return "app/code/modules"	

def controller_base():
	return "app/code/controllers"

def view_base():
	return "app/code/views"

def template_base():
	return "app/design/template"

def mkdir(path):
	parts = path.split("/")
	part = ""
	
	for x in parts:
		if x:
			if part != "":
				part = part + "/" + x
			else:
				part = x
			
			path = part+"/"
		
			if os.path.isdir(path) == False:
				os.mkdir(path)

def prompt(prompt):
    while True:
        ok = raw_input(prompt).lower()
        if ok in ('', 'y', 'ye', 'yes'):
            return True
        if ok in ('!', 'n', 'no', 'nop', 'nope'):
            return False
        print 'Please type either yes or no (Y/n default: Y): '
