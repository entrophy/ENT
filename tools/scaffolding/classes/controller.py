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

		self.path = self.path.replace('_', '/')
		self.actions = self.actions.replace(' ', '')

		self.buildController()

		for action in self.actions.split(','):
			self.buildView(action)
			self.buildTemplate(action)

	def buildController(self):
		
		dest_file = self.get_dest_file()
		dest_path = self.get_dest_path()

		if dest_path != '':
			dest_path = controller_base()+'/'+self.get_section()+'/'+dest_path
		else:
			dest_path = controller_base()+'/'+self.get_section()

		controller_path = dest_path+"/"+dest_file+'Controller.php'

		if not os.path.isfile(controller_path):
			template = Template('controller/controller.txt')
			mkdir(dest_path)

			data = {
				"{{class_path}}": self.get_class_path(),
				"{{section}}": self.get_section(),
				"{{actions}}": self.get_actions()
			}

			template.replace(data)
			template.write(controller_path)
		else:
			f = open(controller_path, 'r+')
			lines = f.readlines()
			f.seek(0)
			
			for action in self.actions.split(','):
				signature = "function "+action.lower()+"Action"
				content = ''.join(lines)

				if not signature in content:
					template_action = Template('controller/action.txt')
					template_action.replace({
						'{{action}}': action
					})

					lines.insert(-3, template_action.get_content())

					print 'Action "'+action+'Action" added to "'+controller_path+'"'

			f.writelines(lines)

			f.close()

		print ""

	def buildView(self, action):
		dest_file = self.get_dest_file()
		dest_path = self.get_dest_path()
		action = string.capwords(action)
		
		if dest_path != '':
			dest_path = view_base()+'/'+self.get_section()+'/'+dest_path+'/'+dest_file
		else:
			dest_path = view_base()+'/'+self.get_section()+'/'+dest_file

		view_path = dest_path+'/'+action+'.php'

		if prompt('Do you want to build the view "'+view_path+'" (Y/N): '):
			template= Template('controller/view.txt')
			
			mkdir(dest_path)
			
			data = {
				"{{class_path}}": self.get_class_path()+'_'+action,
				"{{section}}": self.get_section()
			}

			template.replace(data)
			template.write(view_path)
		else:
			print view_path+' - Not written.'

		print ""
				
	def buildTemplate(self, action):
		dest_file = self.get_dest_file().lower()
		dest_path = self.get_dest_path().lower()
		section = self.get_section().lower()
		action = action.lower()
		
		if dest_path != '':
			dest_path = template_base()+'/'+self.section+'/'+dest_path+'/'+dest_file
		else:
			dest_path = template_base()+'/'+self.section+'/'+dest_file

		template_path = dest_path+'/'+action+'.phtml'

		if prompt('Do you want to build the template "'+template_path+'" (Y/N): '):
			template = Template('controller/template.txt')
			
			mkdir(dest_path)
			
			data = {
				"{{template_path}}": section+'/'+self.get_class_path().replace('_', '/').lower()+'/'+action
			}

			template.replace(data)
			template.write(template_path)
		else:
			print template_path+' - Not written.'

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

		for action in actions:
			result += self.get_action(action)

		return result

	def get_action(self, action):
		template = Template('controller/action.txt')

		data = {
			"{{action}}": action.lower()
		}

		template.replace(data)

		return template.get_content()
		
