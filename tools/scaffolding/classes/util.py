import os
import inspect
import string
import sys

def base():
	result = os.path.abspath(inspect.getfile(inspect.currentframe()))
	result = result.replace("/scaffolding", "")

	return result

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
