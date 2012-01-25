import os
import inspect
import string
import sys

from scaffolding.classes.util import *

class Template:
	def __init__(self, path):
		self.read(path)

	def read(self, path):
		path = root()+"/tools/scaffolding/templates/"+path
		
		if (os.path.isfile(path)):
			f = open(path, 'r')
			self.content = f.read()
			f.close()
		else:
			print path + " could not be located - Exiting."
			sys.exit(1)

	def replace(self, data):
		for key, value in data.items():
			self.content = self.content.replace(key, value)

	def get_content(self):
		return self.content

	def write(self, path):
		if (not os.path.isfile(path)):
			f = open(path, 'w')
			f.write(self.content)
			f.close()

			print path + " - Written."
		else:
			print path + " does already exists - Not writing file."

