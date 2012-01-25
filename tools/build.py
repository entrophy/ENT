#! /usr/bin/env python
import os;
import shutil;

ignore = ['.', '..', '.git', 'README.md'];

root = os.path.abspath(os.path.realpath(__file__)).replace('/tools/build.py', '');
lib = os.path.join(root, 'lib', '');
vendors = os.listdir(lib);

def test(base, dirname, names):
	'''basename = dirname.replace(base, '');
	print(lib);
	delete = [];
	
	for name in names:
		if name in ignore:
			delete.append(names.index(name));
		else:
			if (basename != '' and os.path.isdir(os.path.join(dirname, name))):
				print(basename);
				print(":D"+os.path.join(dirname, name));
	
	delete.sort();
	delete.reverse();
	
	for index in delete:
		del[names[int(index)]];
	'''
		
	
	

for vendor in vendors:
	remotes = os.listdir(lib+vendor+'/remotes/');
	for remote in remotes:

		remote_path = os.path.join(lib+vendor, 'remotes', remote, '');
		for root, dirs, files in os.walk(remote_path):
			for _dir in dirs:
				if (_dir in ignore):
					dirs.remove(_dir);
				else:
					dst = os.path.join(lib+vendor, root.replace(remote_path, ''), _dir);
					if (os.path.isdir(dst) == False):
						os.makedirs(dst);
					
			for _file in files:
				if (_file in ignore):
					files.remove(_file);
				else:
					dst = os.path.join(lib+vendor, root.replace(remote_path, ''), _file);
					shutil.copy(os.path.join(root, _file), dst);

print("ENT built");
