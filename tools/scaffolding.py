#! /usr/bin/env python
import os
import inspect
import string
import sys
#print os.environ

from scaffolding.classes.controller import Controller
from scaffolding.classes.module import Module
from scaffolding.classes.template import Template
from scaffolding.classes.util import *

class Scaffolding:
	def __init__(self, type):
		self.type = type

		if self.type == 'module':
			Module()
		elif self.type == 'controller':
			Controller()
		else:
			print "Unknown type - Exiting."
			sys.exit(1)
			

def main():
	Scaffolding(sys.argv[1])


if __name__ == '__main__':
	main()


