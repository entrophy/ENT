import os
import inspect
import string
import sys

from scaffolding.classes.util import *

import scaffolding.classes.template as Template
Template = Template.Template

class Controller:
	def __init__(self):
		self.section = raw_input("Section (e.g. main): ")
		self.path = raw_input("Controller path (e.g. my/awesome/controller): ")
		self.actions = raw_input("Actions (e.g. view, edit, save): ")
		print ""

		self.actions = self.actions.replace(' ', '')

		self.template_controller = Template('scaffolding/templates/controller/controller.txt')

		self.buildController()
		self.buildTemplate()
		print ""

	def buildController(self):
		dest_file = self.get_dest_file()
		dest_path = self.get_dest_path()

		if dest_path != '':
			dest_path = controller_base()+'/'+self.get_section()+'/'+dest_path
		else:
			dest_path = controller_base()+'/'+self.get_section()

		mkdir(dest_path)

		data = {
			"{{class_path}}": self.get_class_path(),
			"{{section}}": self.get_section(),
			"{{actions}}": self.get_actions()
		}

		self.template_controller.replace(data)
		self.template_controller.write(dest_path+"/"+dest_file+'Controller.php')

	def buildTemplate(self):
		print ""


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

	def get_section(self):
		section = self.section
		section = string.capwords(section)

		return section

	def get_actions(self):
		actions = self.actions
		actions = actions.split(',')

		result = ''

		count = len(actions)
		x = 1;
		for action in actions:
			if x != 1:
				result += "\t"
				
			result += "public function "+action+"Action() {\r\n\r\n \t}"

			if x != count:
				result += "\r\n\r\n"

			x += 1

		return result
