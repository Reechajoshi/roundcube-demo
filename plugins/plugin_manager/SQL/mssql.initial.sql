IF NOT EXISTS (SELECT * FROM sysobjects WHERE id = object_id(N'[dbo].[plugin_manager]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
CREATE TABLE [plugin_manager] (
  [id] int NOT NULL IDENTITY(1,1),
  [conf] nvarchar(MAX) NOT NULL,
  [value] nvarchar(MAX),
  [type] nvarchar(MAX),
  PRIMARY KEY ([id])
)
GO

IF NOT EXISTS (SELECT * FROM sysobjects WHERE id = object_id(N'[dbo].[system]') AND OBJECTPROPERTY(id, N'IsUserTable') = 1)
CREATE TABLE [system] (
 [name] nvarchar(64) NOT NULL,
 [value] nvarchar(MAX),
 PRIMARY KEY([name])
)
GO

INSERT INTO [system] (name, value) VALUES ('myrc_plugin_manager', 'initial|20131209')
GO