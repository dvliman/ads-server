# = Bannerfish =
# Copyright (c) 2002 Itamar Shtull-Trauring, all rights reserved.
# 
# This application is free software; you can redistribute it and/or
# modify it under the terms of version 2 of the GNU General Public
# License as published by the Free Software Foundation.
# 
# This application is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this application; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

"""distutils installation for bannerfish."""

# system imports
from distutils.core import setup
from distutils.command.install_data import install_data
import os

# bannerfish imports
from bannerfish import copyright


class install_data_bannerfish(install_data):
    """Make sure data files are installed in bannerfish package."""
    def finalize_options (self):
        self.set_undefined_options('install',
            ('install_lib', 'install_dir')
        )
        install_data.finalize_options(self)


setup(name="Bannerfish",
      version=copyright.version,
      description="Banner ad server.",
      author="Itamar Shtull-Trauring",
      author_email="itamar@itamarst.org",
      url="http://itamarst.org/software/bannerfish",
      packages = ["bannerfish", "bannerfish.test"],
      cmdclass = {'install_data': install_data_bannerfish},
      data_files = [('bannerfish', [os.path.join('bannerfish', 'plugins.tml')]),]
      )
