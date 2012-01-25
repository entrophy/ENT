import os
import inspect
import string
import sys

from scaffolding.classes.util import *

import scaffolding.classes.template as Template
Template = Template.Template

class Module:
	def __init__(self):
		self.path = raw_input("Module path (e.g. module/path): ")
		self.properties = raw_input("Properties (e.g. key, key, key): ")
		self.table = raw_input("Database table (e.g. table_name): ")
		
		print ""

		self.path = self.path.replace('_', '/')
		self.properties = self.properties.replace(' ', '')

		if self.table == '':
			self.table = self.get_class_path().lower()

		self.templates = [
			{ 'template': Template('module/module.txt'),
				'ending': '.php' },
			{ 'template': Template('module/valueobject.txt'),
				'ending': '/ValueObject.php' },
			{ 'template': Template('module/dataaccessobject.txt'),
				'ending': '/DAO.php' },
			{ 'template': Template('module/collection.txt'),
				'ending': '/Collection.php' },
			{ 'template': Template('module/load.txt'),
				'ending': '/Load.php' }
		]

		self.build()
		print ""

	def build(self):
		dest_file = self.get_dest_file()
		dest_path = self.get_dest_path()

		if dest_path != '':
			dest_path = module_base()+'/'+dest_path
		else:
			dest_path = module_base()

		mkdir(dest_path+"/"+dest_file)

		data = {
			"{{class_path}}": self.get_class_path(),
			"{{table}}": self.get_table(),
			"{{fields}}": self.get_fields(),
			"{{properties}}": self.get_properties(),
			"{{key}}": self.get_key()
		}

		for item in self.templates:
			template = item['template']
			ending = item['ending']

			template.replace(data)
			template.write(dest_path+"/"+dest_file+ending)

	def get_dest_file(self):
		dest_file = self.path
		dest_file = dest_file.split('/')
		dest_file = dest_file[-1]
		dest_file = ''.join(dest_file)
		dest_file = string.capwords(dest_file)

		return dest_file

	def get_dest_path(self):
		dest_path = self.path
		dest_path = dest_path.split('/')
		dest_path = dest_path[:-1]
		dest_path = ' '.join(dest_path)
		dest_path = string.capwords(dest_path)
		dest_path = dest_path.replace(' ', '/')

		return dest_path

	def get_class_path(self):
		class_path = self.path
		class_path = class_path.replace('/', ' ')
		class_path = string.capwords(class_path)
		class_path = class_path.replace(' ', '_')

		return class_path

	def get_table(self):
		return "'"+self.table+"'"

	def get_key(self):
		key = self.path
		key = "'"+key.lower()+"'"

		return key
	
	def get_fields(self):
		result = ''
		
		properties = self.properties.split(',')
		count = len(properties)
		
		x = 1
		for prop in properties:
			if x != 1:
				result += "\t\t";
				
			result += "'"+prop+"'"
			
			if x != count:
				result += ",\r\n"
				
			x += 1

		return result

	def get_properties(self):
		result = ''

		properties = self.properties.split(',')
		count = len(properties)
		
		x = 1
		for prop in properties:
			if x != 1:
				result += "\t";
				
			result += "public $"+prop+";"

			if x != count:
				result += "\r\n"
				
			x += 1

		return result
